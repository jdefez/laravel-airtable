<?php

namespace AxelDotDev\LaravelAirtable\Parameters;

class Parameters
{
    public const MAX = 100;

    public ?int $maxRecords = null;

    public ?int $pageSize = null;

    public ?array $fields = null;

    public string $view = 'Grid view';

    public string|bool $offset = false;

    private ?array $sorters = null;

    private function setSize(int $size): int
    {
        return $size <= self::MAX ? $size : self::MAX;
    }

    public function setMaxRecords(int $size): void
    {
        $this->maxRecords = $this->setSize($size);
    }

    public function setPageSize(int $size): void
    {
        $this->pageSize = $this->setSize($size);
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function setSort(string $field, ?string $direction = 'asc'): void
    {
        $this->sorters[] = new Sort($field, $direction);
    }

    public function setOffset(string|bool $offset): void
    {
        $this->offset = $offset;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    private function getSorters(): ?array
    {
        if (empty($this->sorters)) {
            return null;
        }

        $return = [];

        foreach ($this->sorters as $sort) {
            $return[] = [
                'field' => $sort->field,
                'direction' => $sort->direction,
            ];
        }

        return $return;
    }

    public function toArray(): ?array
    {
        $return = array_filter(
            [
                'maxRecords' => $this->maxRecords,
                'pageSize' => $this->pageSize,
                'fields' => $this->fields,
                'offset' => $this->offset,
                'view' => $this->view,
                'sort' => $this->getSorters(),
            ],
            fn ($item) => !empty($item)
        );

        if (empty($return)) {
            return null;
        }

        return $return;
    }
}
