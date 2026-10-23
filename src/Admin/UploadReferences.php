<?php

declare(strict_types=1);

namespace App\Admin;

/**
 * Finds what the database still refers to an upload file by (design D6).
 *
 * A fixed list of columns that hold a file name, each relative to a folder of
 * the upload folder. The answer is best effort: free text, such as the body of
 * an article, and the tables of third-party modules are not searched.
 */
final class UploadReferences
{
    /**
     * @var list<array{
     *     label: string,
     *     table: string,
     *     column: string,
     *     base: string
     * }>
     */
    private const SOURCES = [
        ['label' => '_CO_ICMS_ASSETS_USED_AS_USER_AVATAR', 'table' => 'users', 'column' => 'user_avatar', 'base' => ''],
        ['label' => '_CO_ICMS_ASSETS_USED_AS_AVATAR', 'table' => 'avatar', 'column' => 'avatar_file', 'base' => ''],
        ['label' => '_CO_ICMS_ASSETS_USED_AS_SMILIE', 'table' => 'smiles', 'column' => 'smile_url', 'base' => ''],
        ['label' => '_CO_ICMS_ASSETS_USED_AS_IMAGE', 'table' => 'image', 'column' => 'image_name', 'base' => 'imagemanager/'],
        ['label' => '_CO_ICMS_ASSETS_USED_AS_RANK', 'table' => 'ranks', 'column' => 'rank_image', 'base' => 'system/userrank/'],
    ];

    /**
     * @param string $relativePath path of the file below the upload folder, with `/`
     *
     * @return list<string> the kinds that use the file, as texts; empty when nothing does
     */
    public function find(string $relativePath): array
    {
        $database = \icms::$xoopsDB;
        $kinds = [];

        foreach (self::SOURCES as $source) {
            if ($source['base'] !== '' && !str_starts_with($relativePath, $source['base'])) {
                continue;
            }

            $fileName = substr($relativePath, strlen($source['base']));
            $sql = sprintf(
                'SELECT 1 FROM %s WHERE %s = %s LIMIT 1',
                $database->prefix($source['table']),
                $source['column'],
                $database->quoteString($fileName),
            );

            $result = $database->query($sql);
            if ($result === false || $database->getRowsNum($result) === 0) {
                continue;
            }

            $kinds[] = constant($source['label']);
        }

        return $kinds;
    }
}
