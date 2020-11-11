<?php
/**
 * Copyright 2020 Cloud Creativity Limited
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

namespace LaravelJsonApi\Eloquent\Fields\Relations;

use LaravelJsonApi\Contracts\Schema\Relation as RelationContract;
use LaravelJsonApi\Contracts\Schema\SchemaAware as SchemaAwareContract;
use LaravelJsonApi\Core\Schema\Concerns\EagerLoadable;
use LaravelJsonApi\Core\Schema\Concerns\Filterable;
use LaravelJsonApi\Core\Schema\Concerns\SparseField;
use LaravelJsonApi\Core\Schema\SchemaAware;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Schema;
use LogicException;
use function sprintf;

abstract class Relation implements RelationContract, SchemaAwareContract
{

    use EagerLoadable;
    use Filterable;
    use SchemaAware;
    use SparseField;

    /**
     * The JSON API field name.
     *
     * @var string
     */
    private string $name;

    /**
     * The name of the relation on the model.
     *
     * @var string|null
     */
    private ?string $relation;

    /**
     * The inverse JSON API resource type.
     *
     * @var string|null
     */
    private ?string $inverse = null;

    /**
     * Guess the inverse resource type.
     *
     * @return string
     */
    abstract protected function guessInverse(): string;

    /**
     * Relation constructor.
     *
     * @param string $fieldName
     * @param string|null $relation
     */
    public function __construct(string $fieldName, string $relation = null)
    {
        $this->name = $fieldName;
        $this->relation = $relation;
    }

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get the name of the relation on the model.
     *
     * @return string
     */
    public function relationName(): string
    {
        if ($this->relation) {
            return $this->relation;
        }

        return $this->relation = $this->guessRelationName();
    }

    /**
     * Set the inverse resource type.
     *
     * @param string $resourceType
     * @return $this
     */
    public function inverseType(string $resourceType): self
    {
        $this->inverse = $resourceType;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function inverse(): string
    {
        if ($this->inverse) {
            return $this->inverse;
        }

        return $this->inverse = $this->guessInverse();
    }

    /**
     * Get the schema for the inverse resource type.
     *
     * @return Schema
     */
    public function schema(): Schema
    {
        $schema = $this->schemas()->schemaFor(
            $this->inverse()
        );

        if ($schema instanceof Schema) {
            return $schema;
        }

        throw new LogicException(sprintf(
            'Expecting inverse schema for resource type %s to be an Eloquent schema.',
            $this->inverse()
        ));
    }

    /**
     * @inheritDoc
     */
    public function toMany(): bool
    {
        return !$this->toOne();
    }

    /**
     * Guess the relation name on the model.
     *
     * @return string
     */
    private function guessRelationName(): string
    {
        return Str::camel($this->name());
    }
}