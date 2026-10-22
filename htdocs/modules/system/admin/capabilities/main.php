<?php
/**
 * ImpressCMS Capabilities administration (design D8, D9)
 *
 * - Providers: every capability with its state and providers; choosing the
 *   active provider (a switch is confirmed); rebuilding the registry
 * - Assignments: the matrix of IPF object types and capabilities
 *
 * Every change is a POST with the ImpressCMS security token.
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		System
 * @subpackage	Capabilities
 */

use Icms\Capability\Definitions;
use Icms\Capability\Registry;
use Icms\Capability\State;

defined('ICMS_ROOT_PATH') or die('ImpressCMS root path not defined');

icms_loadLanguageFile('core', 'capability');

$registry = Registry::instance();
$self = 'admin.php?fct=capabilities';
$op = isset($_POST['op']) ? (string) $_POST['op'] : (isset($_GET['op']) ? (string) $_GET['op'] : 'providers');

/**
 * Escape a value for HTML output
 */
function capabilities_e($value): string {
	return htmlspecialchars((string) $value, ENT_QUOTES);
}

/**
 * The state label of a capability
 */
function capabilities_state(State $state): string {
	return match ($state) {
		State::Active => _MD_AM_CAPABILITY_STATE_ACTIVE,
		State::Unresolved => _MD_AM_CAPABILITY_STATE_UNRESOLVED,
		State::Unavailable => _MD_AM_CAPABILITY_STATE_UNAVAILABLE,
	};
}

/**
 * Links to the two tabs of the page
 */
function capabilities_tabs(string $self, string $current): string {
	$tabs = array('providers' => _MD_AM_CAPABILITIES_PROVIDERS, 'assignments' => _MD_AM_CAPABILITIES_ASSIGNMENTS);
	$links = array();
	foreach ($tabs as $op => $caption) {
		$links[] = $op === $current
			? '<strong>' . capabilities_e($caption) . '</strong>'
			: '<a href="' . $self . '&amp;op=' . $op . '">' . capabilities_e($caption) . '</a>';
	}
	return '<p id="capability-tabs">' . implode(' | ', $links) . '</p>';
}

if (in_array($op, array('select', 'select_ok', 'saveassignments', 'rebuild'), true)) {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !icms::$security->check()) {
		redirect_header($self, 3, implode('<br />', icms::$security->getErrors() ?: array(_MD_AM_CAPABILITY_TOKEN_INVALID)));
	}
}

switch ($op) {
	case 'rebuild':
		$snapshot = Registry::rebuild();
		$rejected = 0;
		foreach ($snapshot->rejected() as $rejections) {
			$rejected += count($rejections);
		}
		redirect_header($self, 2, sprintf(_MD_AM_CAPABILITIES_REBUILT, $rejected));
		break;

	case 'select':
	case 'select_ok':
		$capability = isset($_POST['capability']) ? (string) $_POST['capability'] : '';
		$provider = isset($_POST['provider']) ? (string) $_POST['provider'] : '';
		if (!Definitions::has($capability) || !in_array($provider, $registry->snapshot()->compatibleProviders($capability), true)) {
			redirect_header($self, 3, _MD_AM_CAPABILITY_PROVIDER_INVALID);
		}

		$active = $registry->activeProviderOf($capability);
		$assigned = $registry->assignments()->assignedTypes($capability);
		if ($op === 'select' && $active !== null && $active !== $provider && count($assigned) > 0) {
			// a switch with assigned object types: the data stays with the previous provider
			icms_cp_header();
			$message = sprintf(
				_MD_AM_CAPABILITY_SWITCH_CONFIRM,
				'<strong>' . capabilities_e(Definitions::title($capability)) . '</strong>',
				'<strong>' . capabilities_e($registry->snapshot()->moduleName($active)) . '</strong>',
				'<strong>' . capabilities_e($registry->snapshot()->moduleName($provider)) . '</strong>'
			);
			$message .= '<ul id="capability-switch-types"><li>' . implode('</li><li>', array_map('capabilities_e', $assigned)) . '</li></ul>';
			$message .= '<p>' . sprintf(_MD_AM_CAPABILITY_SWITCH_DATA, '<strong>' . capabilities_e($registry->snapshot()->moduleName($active)) . '</strong>') . '</p>';
			icms_core_Message::confirm(
				array('fct' => 'capabilities', 'op' => 'select_ok', 'capability' => $capability, 'provider' => $provider),
				$self,
				$message,
				_MD_AM_CAPABILITY_SWITCH
			);
			icms_cp_footer();
			exit();
		}

		$registry->selectProvider($capability, $provider, (int) icms::$user->getVar('uid'));
		redirect_header($self, 2, sprintf(_MD_AM_CAPABILITY_SELECTED, capabilities_e($registry->snapshot()->moduleName($provider)), capabilities_e(Definitions::title($capability))));
		break;

	case 'saveassignments':
		$submitted = isset($_POST['assign']) && is_array($_POST['assign']) ? $_POST['assign'] : array();
		$snapshot = $registry->snapshot();
		$handler = $registry->assignmentHandler();

		foreach ($submitted as $objectType => $capabilities) {
			foreach (is_array($capabilities) ? array_keys($capabilities) : array() as $capability) {
				if ($snapshot->isUnsupported((string) $objectType, (string) $capability)) {
					redirect_header($self . '&amp;op=assignments', 3, sprintf(_MD_AM_CAPABILITY_UNSUPPORTED, capabilities_e($objectType), capabilities_e(Definitions::has((string) $capability) ? Definitions::title((string) $capability) : $capability)));
				}
			}
		}

		foreach ($snapshot->objectTypes() as $objectType) {
			foreach (Definitions::names() as $capability) {
				if ($snapshot->isUnsupported($objectType, $capability) || $registry->stateOf($capability) === State::Unavailable) {
					// not editable: existing assignments are kept as they are
					continue;
				}
				$enabled = isset($submitted[$objectType][$capability]);
				if ($enabled !== $registry->assignments()->isAssigned($objectType, $capability) || ($enabled && $handler->cell($objectType, $capability) === null)) {
					$handler->setCell($objectType, $capability, $enabled);
				}
			}
		}
		// the next reads in this request see the stored matrix
		$handler->matrix(true);
		redirect_header($self . '&amp;op=assignments', 2, _MD_AM_CAPABILITIES_SAVED);
		break;

	case 'assignments':
		icms_cp_header();
		$snapshot = $registry->snapshot();
		echo '<h1>' . capabilities_e(_MD_AM_CAPABILITIES) . '</h1>' . capabilities_tabs($self, 'assignments');
		echo '<p>' . _MD_AM_CAPABILITIES_ASSIGNMENTS_DSC . '</p>';
		echo '<form method="post" action="' . $self . '" id="capability-assignments"><input type="hidden" name="op" value="saveassignments" />';
		echo '<table class="outer" cellspacing="1"><tr><th>' . _MD_AM_CAPABILITY_OBJECT_TYPE . '</th>';
		foreach (Definitions::names() as $capability) {
			$state = $registry->stateOf($capability);
			echo '<th data-capability="' . capabilities_e($capability) . '">' . capabilities_e(Definitions::title($capability))
				. ($state === State::Unavailable ? '<br /><small>' . capabilities_e(capabilities_state($state)) . '</small>' : '') . '</th>';
		}
		echo '</tr>';
		foreach ($snapshot->objectTypes() as $i => $objectType) {
			echo '<tr class="' . ($i % 2 ? 'even' : 'odd') . '"><td class="head">' . capabilities_e($objectType) . '</td>';
			foreach (Definitions::names() as $capability) {
				$unsupported = $snapshot->isUnsupported($objectType, $capability);
				$unavailable = $registry->stateOf($capability) === State::Unavailable;
				$checked = $registry->assignments()->isAssigned($objectType, $capability) ? ' checked="checked"' : '';
				$disabled = $unsupported || $unavailable ? ' disabled="disabled"' : '';
				$title = $unsupported ? ' title="' . capabilities_e(_MD_AM_CAPABILITY_NOT_SUPPORTED) . '"' : '';
				echo '<td align="center"><input type="checkbox" name="assign[' . capabilities_e($objectType) . '][' . capabilities_e($capability) . ']" value="1"' . $checked . $disabled . $title . ' /></td>';
			}
			echo '</tr>';
		}
		echo '</table>' . icms::$security->getTokenHTML() . '<p><button type="submit">' . _MD_AM_CAPABILITIES_SAVE . '</button></p></form>';
		icms_cp_footer();
		break;

	case 'providers':
	default:
		icms_cp_header();
		$snapshot = $registry->snapshot();
		echo '<h1>' . capabilities_e(_MD_AM_CAPABILITIES) . '</h1>' . capabilities_tabs($self, 'providers');
		echo '<form method="post" action="' . $self . '" id="capability-rebuild"><input type="hidden" name="op" value="rebuild" />'
			. icms::$security->getTokenHTML() . '<button type="submit">' . _MD_AM_CAPABILITIES_REBUILD . '</button></form>';

		foreach (Definitions::names() as $capability) {
			$state = $registry->stateOf($capability);
			$selected = $registry->selectionOf($capability);
			$active = $registry->activeProviderOf($capability);
			echo '<div class="capability" id="capability-' . capabilities_e($capability) . '" data-state="' . $state->value . '">';
			echo '<h2>' . capabilities_e(Definitions::title($capability)) . ' <small>' . sprintf(_MD_AM_CAPABILITY_CONTRACT, capabilities_e(Definitions::version($capability)))
				. ' &middot; ' . capabilities_e(capabilities_state($state)) . '</small></h2>';

			if ($selected !== null && $active === null) {
				echo '<p class="errorMsg">' . sprintf(_MD_AM_CAPABILITY_SELECTED_GONE, capabilities_e($selected)) . '</p>';
			}

			$providers = $snapshot->providers($capability);
			if (count($providers) === 0) {
				echo '<p>' . _MD_AM_CAPABILITY_NO_PROVIDER . '</p></div>';
				continue;
			}

			echo '<form method="post" action="' . $self . '"><input type="hidden" name="op" value="select" /><input type="hidden" name="capability" value="' . capabilities_e($capability) . '" />';
			echo '<table class="outer" cellspacing="1"><tr><th></th><th>' . _MD_AM_CAPABILITY_PROVIDER . '</th><th>' . _MD_AM_CAPABILITY_MODULE_VERSION . '</th><th>' . _MD_AM_CAPABILITY_CONTRACT_VERSION . '</th><th></th></tr>';
			foreach ($providers as $module => $provider) {
				$adminUrl = null;
				if ($provider['compatible']) {
					try {
						$adminUrl = $registry->providerInstance($capability, $module)?->adminUrl();
					} catch (Throwable $e) {
						$adminUrl = null;
					}
				}
				echo '<tr class="provider" data-provider="' . capabilities_e($module) . '"' . ($module === $active ? ' data-active="1"' : '') . '>';
				echo '<td><input type="radio" name="provider" value="' . capabilities_e($module) . '"'
					. ($module === $active ? ' checked="checked"' : '') . ($provider['compatible'] ? '' : ' disabled="disabled"') . ' /></td>';
				echo '<td>' . capabilities_e($snapshot->moduleName($module)) . ($module === $active ? ' <strong>(' . _MD_AM_CAPABILITY_STATE_ACTIVE . ')</strong>' : '') . '</td>';
				echo '<td>' . capabilities_e($snapshot->moduleVersion($module)) . '</td>';
				echo '<td>' . capabilities_e($provider['version'])
					. ($provider['compatible'] ? ' ' . _MD_AM_CAPABILITY_COMPATIBLE : ' <span class="incompatible">' . sprintf(_MD_AM_CAPABILITY_INCOMPATIBLE, capabilities_e($provider['reason'])) . '</span>') . '</td>';
				echo '<td>' . ($adminUrl !== null ? '<a class="provider-admin" href="' . capabilities_e($adminUrl) . '">' . _MD_AM_CAPABILITY_MANAGE . '</a>' : '') . '</td>';
				echo '</tr>';
			}
			echo '</table>' . icms::$security->getTokenHTML() . '<p><button type="submit">' . _MD_AM_CAPABILITY_SAVE_SELECTION . '</button></p></form></div>';
		}

		$rejected = $snapshot->rejected();
		if (count($rejected) > 0) {
			echo '<h2>' . _MD_AM_CAPABILITIES_REJECTED . '</h2><ul id="capability-rejected">';
			foreach ($rejected as $module => $rejections) {
				foreach ($rejections as $rejection) {
					echo '<li>' . capabilities_e($module) . '/capability/' . capabilities_e(basename($rejection['file'])) . ': ' . capabilities_e($rejection['reason']) . '</li>';
				}
			}
			echo '</ul>';
		}
		icms_cp_footer();
		break;
}
