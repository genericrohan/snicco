<?php

namespace Snicco\Testing\Concerns;

use Snicco\Core\Support\WP;
use Snicco\Core\Support\Url;

trait BuildsWordPressUrls
{
    
    protected function baseUrl() :string
    {
        return 'https://example.com';
    }
    
    final protected function adminUrlTo(string $menu_slug, string $parent_page = 'admin.php') :string
    {
        return Url::combineAbsPath(
            $this->baseUrl(),
            WP::wpAdminFolder().'/'.$parent_page.'?page='.$menu_slug
        );
    }
    
}