<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

use function end;
use function explode;
use function trim;

/**
 * The iterator that grabs rows by chunk and safe for changing/deleting rows
 * while iteration.
 *
 * Similar to {@link \Illuminate\Database\Query\Builder::chunkById()} but uses
 * generators instead of {@link \Closure}. Although you can modify/delete the
 * items while iteration there are few important limitations:
 *
 * - it is not possible to sort rows, they always will be sorted by `column asc`;
 * - the `column` should not be changed while iteration or this may lead to
 *   repeating row in results;
 * - the row inserted while iteration may be skipped if it has `column` with
 *   the value that lover than the internal pointer;
 * - queries with UNION is not supported.
 *
 * @see      https://github.com/laravel/framework/issues/35400
 *
 * @template TItem of \Illuminate\Database\Eloquent\Model
 *
 * @extends IteratorImpl<TItem>
 */
class ChunkedChangeSafeIterator extends IteratorImpl {
    private string $column;

    public function __construct(Builder $builder, string $column = null) {
        parent::__construct($builder);

        $this->column = $column ?? $this->getDefaultColumn($builder);

        // Unfortunately the Laravel doesn't correctly work with UNION,
        // it just adds conditional to the main query, and this leads to an
        // infinite loop.
        if ($this->hasUnions()) {
            throw new InvalidArgumentException('Query with UNION is not supported.');
        }
    }

    public function getColumn(): string {
        return $this->column;
    }

    protected function getChunk(Builder $builder, int $chunk): Collection {
        $column  = $this->getColumn();
        $builder = $builder->reorder()->orderBy($column)->limit($chunk);

        if ($this->getOffset()) {
            $builder->where($column, '>', $this->getOffset());
        }

        return $builder->get();
    }

    protected function chunkProcessed(Collection $items): bool {
        $last = $this->column($items->last());

        if ($last) {
            $this->setOffset($last);
        }

        return parent::chunkProcessed($items)
            && $last;
    }

    /**
     * @param TItem|null $item
     */
    protected function column(Model|null $item): mixed {
        $value  = null;
        $column = explode('.', $this->getColumn());
        $column = trim(end($column), '`"[]');

        if ($item) {
            $value = $item->getAttribute($column);
        }

        return $value;
    }

    protected function hasUnions(): bool {
        return (bool) $this->getBuilder()->toBase()->unions;
    }

    protected function getDefaultOffset(): ?int {
        // Because Builder contains SQL offset, not column value.
        return null;
    }

    /**
     * @param Builder<TItem> $builder
     */
    protected function getDefaultColumn(Builder $builder): string {
        $column = $builder->getModel()->getKeyName();
        $column = $builder->qualifyColumn($column);

        return $column;
    }
}
