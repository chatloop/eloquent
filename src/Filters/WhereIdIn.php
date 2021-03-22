<?php
/*
 * Copyright 2021 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Eloquent\Filters;

use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Schema\IdParser;
use LaravelJsonApi\Eloquent\Contracts\Filter;

class WhereIdIn implements Filter
{

    use Concerns\HasDelimiter;

    /**
     * @var ID
     */
    private ID $field;

    /**
     * @var string|null
     */
    private ?string $column;

    /**
     * @var string
     */
    private string $key;

    /**
     * Create a new filter.
     *
     * @param Schema $schema
     * @param string|null $key
     * @return static
     */
    public static function make(Schema $schema, string $key = null): self
    {
        return new self(
            $schema->id(),
            $schema->idKeyName(),
            $key,
        );
    }

    /**
     * WhereIdIn constructor.
     *
     * @param ID $field
     * @param string|null $column
     * @param string|null $key
     */
    private function __construct(ID $field, ?string $column, ?string $key)
    {
        $this->field = $field;
        $this->column = $column;
        $this->key = $key ?: 'id';
    }

    /**
     * @inheritDoc
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function apply($query, $value)
    {
        $model = $query->getModel();
        $column = $this->column ?? $model->getRouteKeyName();

        return $query->whereIn(
            $model->qualifyColumn($column),
            IdParser::make($this->field)->decodeIds(
                $this->toArray($value),
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function isSingular(): bool
    {
        return false;
    }

}
