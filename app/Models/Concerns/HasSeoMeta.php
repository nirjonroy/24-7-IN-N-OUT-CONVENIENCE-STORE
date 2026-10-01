<?php

namespace App\Models\Concerns;

trait HasSeoMeta
{
    public function getSeoTitle(): ?string
    {
        return $this->seo_title ?: $this->page_name;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function getMetaTitle(): ?string
    {
        return $this->meta_title ?: $this->getSeoTitle();
    }

    public function getMetaDescription(): ?string
    {
        return $this->meta_description ?: $this->getSeoDescription();
    }

    public function getMetaImage(): ?string
    {
        return $this->meta_image;
    }

    public function getSeoKeywords(): ?string
    {
        return $this->keywords;
    }
}
