<?php
declare(strict_types=1);

/**
 * ImpressCMS - IPF Table View with multi-criteria filters
 *
 * This file extends the IPF table rendering to show multiple filter criteria
 * side-by-side and combines them in a CriteriaCompo (AND by default).
 *
 * Compatible with PHP 7.4 through 8.4.
 *
 * @category  ImpressCMS
 * @package   icms_ipf_view
 */

defined('ICMS_ROOT_PATH') or die('Restricted access');

/**
 * NOTE: This file is based on the structure and conventions in ImpressCMS 2.0.x.
 * If your local version diverges, adjust method names and property access accordingly.
 */

class icms_ipf_view_Table {

    /** @var icms_ipf_Handler */
    protected $_handler;

    /** @var icms_ipf_view_Column[]|array<string, array> */
    protected $_columns = [];

    /** @var CriteriaCompo|null */
    protected $_criteria;

    /** @var string */
    protected $_itemname;

    /** @var string */
    protected $_formTarget;

    /** @var array<string, mixed> */
    protected $_options = [
        // 'filter_logic' => 'AND' or 'OR'
        'filter_logic' => 'AND',
        // 'preserve_query_params' => list of keys to keep when filtering
        'preserve_query_params' => ['start', 'limit', 'sort', 'order'],
    ];

    /**
     * Construct the table view.
     *
     * @param icms_ipf_Handler $handler
     * @param string           $itemname
     */
    public function __construct($handler, string $itemname = '')
    {
        $this->_handler = $handler;
        $this->_itemname = $itemname ?: $handler->getItemName();
        $this->_criteria = new CriteriaCompo();
        $this->_formTarget = $this->resolveFormTarget();
        $this->initializeColumns();
    }

    /**
     * Resolve current form target (URL).
     *
     * @return string
     */
    protected function resolveFormTarget(): string
    {
        $script = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
        $query  = isset($_SERVER['QUERY_STRING']) ? (string) $_SERVER['QUERY_STRING'] : '';
        // We strip existing column filter params below, so the base URL remains stable.
        $base = $script;
        if ($query !== '') {
            $base .= '?' . $query;
        }
        return $base;
    }

    /**
     * Initialize columns from handler or internal configuration.
     * Ensure each column has 'title', 'filter' (bool), and optional 'filter_options'.
     *
     * This is a placeholder to demonstrate how to ensure columns are ready
     * for rendering filters. Adjust to your actual column source.
     */
    protected function initializeColumns(): void
    {
        // In actual ImpressCMS, columns often get set via addColumn(), etc.
        // This method ensures defaults are present for filtering metadata.
        foreach ($this->_columns as $name => &$col) {
            $col = array_merge([
                'title'          => ucfirst((string)$name),
                'filter'         => false,
                'filter_options' => [],
                'filter_type'    => 'select', // 'select' | 'text' | 'date' (extensible)
                'filter_operand' => '=',      // default operand for select/text
            ], $col);
        }
        unset($col);
    }

    /**
     * Public API to add a column (if not already present).
     *
     * @param string $name
     * @param array  $metadata
     * @return void
     */
    public function addColumn(string $name, array $metadata = []): void
    {
        $this->_columns[$name] = array_merge([
            'title'          => ucfirst($name),
            'filter'         => false,
            'filter_options' => [],
            'filter_type'    => 'select',
            'filter_operand' => '=',
        ], $metadata);
    }

    /**
     * Set an option (e.g., filter_logic).
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function setOption(string $key, $value): void
    {
        $this->_options[$key] = $value;
    }

    /**
     * Render the table including filters and data.
     *
     * @return string
     */
    public function render(): string
    {
        // Build combined criteria based on current request filters
        $requestFilters = $this->processRequestFilters();
        $this->applyFiltersToCriteria($requestFilters);

        // Fetch items based on criteria
        $items = $this->fetch();

        // Render via Smarty template (existing ImpressCMS flow)
        // You can adapt this to your current template calls.
        $tpl = new icms_view_Tpl();
        $tpl->assign('table', $this);
        $tpl->assign('items', $items);
        $tpl->assign('filters_html', $this->renderFilters());
        return $tpl->fetch('db:icms_ipf_table.tpl');
    }

    /**
     * Render the multi-criteria filter bar.
     *
     * @return string
     */
    public function renderFilters(): string
    {
        $columns = $this->getFilterableColumns();
        if (empty($columns)) {
            return '';
        }

        $action = $this->sanitizeActionUrl($this->_formTarget, array_keys($columns));
        $html   = [];
        $html[] = '<form method="get" class="icms-ipf-filters" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '">';
        $html[] = '<div class="icms-ipf-filters__row">';

        foreach ($columns as $colName => $col) {
            $current = $this->getRequestValue($colName);
            $label   = htmlspecialchars((string) $col['title'], ENT_QUOTES, 'UTF-8');
            $id      = 'filter_' . htmlspecialchars($colName, ENT_QUOTES, 'UTF-8');
            $name    = htmlspecialchars($colName, ENT_QUOTES, 'UTF-8');

            $html[] = '<div class="icms-ipf-filters__field">';
            $html[] = '<label class="icms-ipf-filters__label" for="' . $id . '">' . $label . '</label>';

            $type = (string) $col['filter_type'];
            if ($type === 'select') {
                $html[] = '<select class="icms-ipf-filters__input" name="' . $name . '" id="' . $id . '">';
                $html[] = '<option value="">--</option>';

                foreach ((array) $col['filter_options'] as $val => $optLabel) {
                    $valStr   = (string) $val;
                    $optLabel = (string) $optLabel;
                    $selected = ($current !== '' && $current === $valStr) ? ' selected' : '';
                    $html[]   = '<option value="' . htmlspecialchars($valStr, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>'
                        . htmlspecialchars($optLabel, ENT_QUOTES, 'UTF-8') . '</option>';
                }
                $html[] = '</select>';
            } elseif ($type === 'text') {
                $html[] = '<input class="icms-ipf-filters__input" type="text" name="' . $name . '" id="' . $id . '" value="'
                    . htmlspecialchars((string) $current, ENT_QUOTES, 'UTF-8') . '">';
            } else {
                // Extensible: date, number, etc.
                $html[] = '<input class="icms-ipf-filters__input" type="text" name="' . $name . '" id="' . $id . '" value="'
                    . htmlspecialchars((string) $current, ENT_QUOTES, 'UTF-8') . '">';
            }

            $html[] = '</div>';
        }

        // Preserve non-filter query params
        foreach ($this->_options['preserve_query_params'] as $keepKey) {
            if (isset($_GET[$keepKey])) {
                $html[] = '<input type="hidden" name="' . htmlspecialchars((string) $keepKey, ENT_QUOTES, 'UTF-8') . '" value="'
                    . htmlspecialchars((string) $_GET[$keepKey], ENT_QUOTES, 'UTF-8') . '">';
            }
        }

        $html[] = '</div>';
        $html[] = '<div class="icms-ipf-filters__actions">';
        $html[] = '<button type="submit" class="button is-primary">' . htmlspecialchars(_SUBMIT, ENT_QUOTES, 'UTF-8') . '</button>';
        $html[] = '<a class="button is-light" href="' . htmlspecialchars($this->clearFiltersUrl(), ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars(_CLEAR, ENT_QUOTES, 'UTF-8') . '</a>';
        $html[] = '</div>';
        $html[] = '</form>';

        return implode('', $html);
    }

    /**
     * Get only the columns that are marked as filterable.
     *
     * @return array<string, array>
     */
    protected function getFilterableColumns(): array
    {
        $out = [];
        foreach ($this->_columns as $name => $col) {
            if (!empty($col['filter'])) {
                $out[$name] = $col;
            }
        }
        return $out;
    }

    /**
     * Remove existing filter params from the action URL so we don't duplicate them.
     *
     * @param string        $url
     * @param array<string> $filterKeys
     * @return string
     */
    protected function sanitizeActionUrl(string $url, array $filterKeys): string
    {
        // Normalize
        $parts = parse_url($url);
        if (!$parts) {
            return $url;
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
            foreach ($filterKeys as $k) {
                unset($query[$k]);
            }
        }
        $scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host     = $parts['host'] ?? '';
        $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path     = $parts['path'] ?? '';
        $newQuery = http_build_query($query);

        $rebuilt = $scheme . $host . $port . $path;
        if ($newQuery !== '') {
            $rebuilt .= '?' . $newQuery;
        }
        return $rebuilt;
    }

    /**
     * Build a URL that clears filters (keeps preserved params only).
     *
     * @return string
     */
    protected function clearFiltersUrl(): string
    {
        $parts = parse_url($this->_formTarget);
        $query = [];
        if ($parts && !empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
        }
        // Remove any filter keys
        foreach ($this->getFilterableColumns() as $k => $_) {
            unset($query[$k]);
        }
        // Keep preserved ones
        $keep = [];
        foreach ($this->_options['preserve_query_params'] as $p) {
            if (isset($query[$p])) {
                $keep[$p] = $query[$p];
            }
        }
        $scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host     = $parts['host'] ?? '';
        $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path     = $parts['path'] ?? '';
        $newQuery = http_build_query($keep);

        $rebuilt = $scheme . $host . $port . $path;
        if ($newQuery !== '') {
            $rebuilt .= '?' . $newQuery;
        }
        return $rebuilt;
    }

    /**
     * Get request value for a filter key as a string.
     *
     * @param string $key
     * @return string
     */
    protected function getRequestValue(string $key): string
    {
        $raw = $_GET[$key] ?? '';
        if (is_array($raw)) {
            return ''; // We expect scalar for now
        }
        $val = trim((string) $raw);
        return $val;
    }

    /**
     * Process request filters based on filterable columns.
     *
     * @return array<string, string> key => value
     */
    protected function processRequestFilters(): array
    {
        $out = [];
        foreach ($this->getFilterableColumns() as $name => $col) {
            $val = $this->getRequestValue($name);
            if ($val !== '') {
                $out[$name] = $val;
            }
        }
        return $out;
    }

    /**
     * Apply request filters to internal criteria (AND or OR).
     *
     * @param array<string, string> $filters
     * @return void
     */
    protected function applyFiltersToCriteria(array $filters): void
    {
        if (empty($filters)) {
            return;
        }

        $logic = strtoupper((string) ($this->_options['filter_logic'] ?? 'AND'));
        if ($logic !== 'OR') {
            $logic = 'AND';
        }

        $compo = new CriteriaCompo(null, $logic);

        foreach ($filters as $field => $value) {
            $meta     = $this->_columns[$field] ?? [];
            $operand  = isset($meta['filter_operand']) ? (string) $meta['filter_operand'] : '=';
            $criteria = $this->buildCriteriaFromFilter($field, $value, $operand, $meta);
            if ($criteria instanceof CriteriaElement) {
                $compo->add($criteria);
            }
        }

        // Add to the table criteria, so pagination and sorting stay compatible
        $this->_criteria->add($compo);
    }

    /**
     * Build criteria element from filter info.
     *
     * @param string $field
     * @param string $value
     * @param string $operand
     * @param array  $meta
     * @return CriteriaElement|null
     */
    protected function buildCriteriaFromFilter(string $field, string $value, string $operand, array $meta)
    {
        // Support LIKE for text filters
        $type = isset($meta['filter_type']) ? (string) $meta['filter_type'] : 'select';
        if ($type === 'text' && $operand === 'LIKE') {
            return new Criteria($field, '%' . $value . '%', 'LIKE');
        }

        // Default equals
        return new Criteria($field, $value, $operand);
    }

    /**
     * Fetch items using handler and current criteria.
     *
     * @return array<int, icms_ipf_Object>|array
     */
    public function fetch(): array
    {
        // The handler should return objects matching criteria; apply sorting/pagination via preserved query
        $start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
        $sort  = isset($_GET['sort']) ? (string) $_GET['sort'] : '';
        $order = isset($_GET['order']) ? strtoupper((string) $_GET['order']) : 'ASC';

        if ($sort !== '') {
            $this->_criteria->setSort($sort);
            $this->_criteria->setOrder($order === 'DESC' ? 'DESC' : 'ASC');
        }
        $this->_criteria->setStart($start);
        $this->_criteria->setLimit($limit);

        return (array) $this->_handler->getObjects($this->_criteria, true);
    }

    /**
     * Expose columns to template.
     *
     * @return array<string, array>
     */
    public function getColumns(): array
    {
        return $this->_columns;
    }

    /**
     * Allow external code to set columns with metadata.
     *
     * @param array<string, array> $columns
     * @return void
     */
    public function setColumns(array $columns): void
    {
        $this->_columns = $columns;
        $this->initializeColumns();
    }

    /**
     * Expose options to template.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->_options;
    }

    /**
     * Expose criteria for debugging or extension.
     *
     * @return CriteriaCompo
     */
    public function getCriteria(): CriteriaCompo
    {
        return $this->_criteria;
    }
}
