<?php

declare(strict_types=1);

namespace Jengo\Schema\Query\OptionBuilder;

use Closure;
use Jengo\Schema\Config\Schema as SchemaConfig;
use Jengo\Schema\Query\DTO\PaginationOptions;
use Jengo\Schema\Query\DTO\ParamOptions;
use Jengo\Schema\Query\DTO\QueryOptions;
use Jengo\Schema\Query\DTO\SelectOptions;
use Jengo\Schema\Support\Utils;

final class RequestOptionsBuilder
{
    private array $reservedQueryWords = [
        'select',
        'orWhere',
        'sort',
        'page',
        'limit',
        'search',
        'derive',
        'null',
        'encrypted',
        'group',
        'withQuery',
        'links'
    ];

    private array $reservedQueryValues = [
        'null'
    ];

    private SchemaConfig $config;

    private array $paramCallbacks = [];

    public static function build(?QueryOptions $options = null): QueryOptions
    {
        $self = new self();
        $self->config = Utils::config();
        $defaultOptions = new QueryOptions();
        $request = request();

        $self->paramCallbacks = [
            ...$self->config->whereCallbacks,
            ...($options?->param->callbacks ?? [])
        ];

        $orWhere = !!$request->getGet('orWhere');

        // get the params from the request
        $params = $self->parseWhere($request->getGet(), !$orWhere);
        $select = explode(',', $request->getGet('select') ?? '') ?? [];
        $derive = explode(',', $request->getGet('derive') ?? '') ?? [];
        $search = $request->getGet('search');
        $sort = $request->getGet('sort');

        // pagination options
        $group = $request->getGet('group');
        $withQuery = $request->getGet('withQuery') !== null ?: $defaultOptions->pagination->withQuery;
        $linksMax = max((int) $request->getGet('links'), $defaultOptions->pagination->linksMax);
        $page = max((int) $request->getGet('page'), 1);
        $limit = $request->getGet('limit') ?: $defaultOptions->pagination->limit;

        return new QueryOptions(
            params: new ParamOptions(
                params: $params,
                callbacks: $self->paramCallbacks
            ),
            pagination: new PaginationOptions(
                limit: $limit,
                page: $page,
                linksMax: $linksMax,
                withQuery: $withQuery,
                group: $group,
            ),
            select: new SelectOptions(
                select: $select,
            ),
            sort: $sort,
            search: $search,
            derive: $derive,
        );
    }

    private function parseWhere(array $wheres, bool $isAndWhere = true): array
    {
        $result = [];
        foreach ($wheres as $key => $value) {
            if (!in_array($key, $this->reservedQueryWords)) {
                if (is_string($key) && is_string($value)) {
                    $out = $this->parseSelectValue($key, $value, $isAndWhere);
                    $result[$out['key']] = $out['value'];
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    protected function unwrapReservedWord(string $value): ?string
    {
        if (str_starts_with($value, '--') && str_ends_with($value, '--')) {
            return substr($value, 2, -2); // remove first and last two characters
        }

        return $value;
    }

    protected function parseSelectValue(string $value, string $key, bool $isAndWhere = true): array
    {
        // before callbacks
        $this->runCallbacks($key, $value, $isAndWhere, 'before');

        // unwrap any reserved words
        if (is_string($value)) {
            $value = trim((string) self::unwrapReservedWord((string) $value));

        }

        if (is_string($key)) {
            $key = trim((string) self::unwrapReservedWord((string) $key));
        }

        // parse any special characters
        if (is_string($value) && str_starts_with($value, '!')) {
            $key .= " !=";
            $value = substr($value, 1);
        }

        if (!in_array($value, $this->reservedQueryValues)) {
            $value;
        }

        if (is_string($value) && $value === 'null') {
            $value = null;
        }

        // after callbacks
        $this->runCallbacks($key, $value, $isAndWhere, 'after');

        return [
            'key' => $key,
            'value' => $value
        ];
    }

    /**
     * Runs select callbacks and adjusts values of key and value accordingly
     * @param string $key
     * @param string $value
     * @param bool $isAndSelect
     * @param string $callTime
     * @return list<string>
     */
    private function runCallbacks(string &$key, string &$value, bool $isAndSelect, string $callTime): void
    {
        $defaultOutput = [$key, $value];
        $output = $defaultOutput;

        foreach ($this->paramCallbacks as $fn) {
            try {
                if ($fn instanceof Closure) {
                    $output = $fn($key, $value, $isAndSelect ? 'and' : 'or', $callTime);
                }
            }

            // check if array is of key - value pair
            if (!is_array($output) || !isset($output[0]) || !isset($output[1])) {
                $output = $defaultOutput;
            }
        }

        $key = $output[0];
        $value = $output[1];
    }
}
