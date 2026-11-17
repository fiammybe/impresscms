<?php

declare(strict_types=1);

namespace App\Controller\Ipf;

use App\Ipf\Api\ApiItem;
use App\Ipf\Api\ApiResource;
use App\Ipf\Api\ResourceRegistry;
use App\Ipf\IpfRepository;
use App\Ipf\Metadata\DefinitionRegistry;
use App\Ipf\Metadata\EntityDefinition;
use App\Ipf\Query\InvalidListQueryException;
use App\Ipf\Query\ListQueryResolver;
use App\Legacy\Attribute\RequiresLegacy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * The read-only JSON API of IPF objects (design D6):
 *
 *     GET /api/ipf/{module}/{item}?q=&sort=&order=&page=&limit=&filter[field]=
 *     GET /api/ipf/{module}/{item}/{id}
 *
 * An object is served only when its module's manifest declares it
 * (App\Ipf\Api\ResourceRegistry), and only to users who hold the declared
 * `read` attribute. Everything else about it (fields, types, search, paging)
 * follows from the object's description: an object needs no endpoint code.
 */
#[RequiresLegacy(stage: 'module')]
final class IpfApiController
{
    private const NAME = '[A-Za-z0-9_]+';

    public function __construct(
        private readonly ResourceRegistry $resources,
        private readonly DefinitionRegistry $definitions,
        private readonly ListQueryResolver $queries,
        private readonly IpfRepository $repository,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokens,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/ipf/{module}/{item}', name: 'ipf_api_list', requirements: ['module' => self::NAME, 'item' => self::NAME], methods: ['GET'])]
    public function list(string $module, string $item, Request $request): Response
    {
        // an object is described only once its module has declared it
        $resource = $this->resources->find($module, $item);
        if ($resource === null) {
            return self::error(Response::HTTP_NOT_FOUND, 'Not found.');
        }

        $denied = $this->denied($resource);
        if ($denied !== null) {
            return $denied;
        }

        $definition = $this->definitions->find($module, $item);
        if ($definition === null) {
            return self::error(Response::HTTP_NOT_FOUND, 'Not found.');
        }

        try {
            $query = $this->queries->fromApiRequest($request, $definition, $resource->search ?: [$definition->identifierField]);
        } catch (InvalidListQueryException $e) {
            return self::error(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }

        $result = $this->repository->list($definition, $query);

        return $this->json([
            'items' => array_map(
                static fn (object $object): ApiItem => new ApiItem($definition, $resource, $object),
                $result->items,
            ),
            'total' => $result->total,
            'page' => $query->page(),
            'limit' => $query->limit,
        ]);
    }

    #[Route('/api/ipf/{module}/{item}/{id}', name: 'ipf_api_detail', requirements: ['module' => self::NAME, 'item' => self::NAME, 'id' => '[^/]+'], methods: ['GET'])]
    public function detail(string $module, string $item, string $id): Response
    {
        // an object is described only once its module has declared it
        $resource = $this->resources->find($module, $item);
        if ($resource === null) {
            return self::error(Response::HTTP_NOT_FOUND, 'Not found.');
        }

        $denied = $this->denied($resource);
        if ($denied !== null) {
            return $denied;
        }

        $definition = $this->definitions->find($module, $item);
        if ($definition === null) {
            return self::error(Response::HTTP_NOT_FOUND, 'Not found.');
        }

        $object = self::isValidId($definition, $id) ? $this->repository->find($definition, $id) : null;
        if ($object === null) {
            return self::error(Response::HTTP_NOT_FOUND, 'Not found.');
        }

        return $this->json(new ApiItem($definition, $resource, $object));
    }

    /**
     * The API only reads: any other method on its addresses is refused here,
     * before it could reach a legacy script.
     */
    #[Route('/api/ipf/{module}/{item}', name: 'ipf_api_list_readonly', requirements: ['module' => self::NAME, 'item' => self::NAME], methods: ['POST', 'PUT', 'PATCH', 'DELETE'])]
    #[Route('/api/ipf/{module}/{item}/{id}', name: 'ipf_api_detail_readonly', requirements: ['module' => self::NAME, 'item' => self::NAME, 'id' => '[^/]+'], methods: ['POST', 'PUT', 'PATCH', 'DELETE'])]
    public function readOnly(): Response
    {
        $response = self::error(Response::HTTP_METHOD_NOT_ALLOWED, 'The API is read-only.');
        $response->headers->set('Allow', 'GET');

        return $response;
    }

    /**
     * The refusal for a user who may not read the resource: 401 for a visitor
     * who is not logged in, 403 otherwise. Neither says anything about the data.
     */
    private function denied(ApiResource $resource): ?Response
    {
        if ($this->authorization->isGranted($resource->read)) {
            return null;
        }

        if ($this->tokens->getToken()?->getUser() === null) {
            return self::error(Response::HTTP_UNAUTHORIZED, 'Authentication required.');
        }

        return self::error(Response::HTTP_FORBIDDEN, 'Not allowed.');
    }

    /**
     * A numeric key takes digits only, so that "12abc" is not read as 12.
     */
    private static function isValidId(EntityDefinition $definition, string $id): bool
    {
        if ($definition->field($definition->keyField)->dataType !== XOBJ_DTYPE_INT) {
            return $id !== '';
        }

        return ctype_digit($id);
    }

    private function json(mixed $data): JsonResponse
    {
        $json = $this->serializer->serialize($data, 'json', ['json_encode_options' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE]);
        $response = new JsonResponse($json, Response::HTTP_OK, [], true);
        // answers depend on who asks
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    private static function error(int $status, string $message): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status, ['Cache-Control' => 'private, no-store']);
    }
}
