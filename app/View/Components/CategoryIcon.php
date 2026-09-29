<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * An icon for the homepage category menu, picked by category name from
 * config/category-icons.php and drawn from the cached sprite
 * resources/images/category-icons.svg. Renders nothing when no icon applies,
 * so the caller decides whether to keep the space.
 */
final class CategoryIcon extends Component
{
    public function __construct(
        public string $name,
        public ?string $parentName = null,
        public string $class = 'w-5 h-5',
    ) {}

    /**
     * The icon key for a main category, or for a first-level subcategory when
     * its parent is given; null when it gets no icon.
     */
    public static function keyFor(string $name, ?string $parentName = null): ?string
    {
        if ($parentName === null) {
            return config("category-icons.roots.{$name}") ?? config('category-icons.default');
        }

        if (in_array($parentName, config('category-icons.children_without_icons', []), true)) {
            return null;
        }

        return config('category-icons.children')[$parentName][$name] ?? self::keyFor($parentName);
    }

    public function shouldRender(): bool
    {
        return $this->key() !== null;
    }

    public function render(): View
    {
        return view('components.category-icon', ['key' => $this->key()]);
    }

    private function key(): ?string
    {
        $key = self::keyFor($this->name, $this->parentName);

        return $key !== null && is_file(resource_path("images/category-icons/{$key}.svg")) ? $key : null;
    }
}
