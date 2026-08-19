<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMenuAccess extends Model
{
    use HasFactory;

    protected $table = 'user_menu_accesses';

    protected $fillable = [
        'user_id',
        'menu_key',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The menu items that can be assigned, from config/menu_access.php.
     */
    public static function items(): array
    {
        return config('menu_access.items', []);
    }

    /**
     * Resolve the menu key that covers a given route name, or null.
     */
    public static function keyForRoute(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        foreach (static::items() as $key => $item) {
            if (in_array($routeName, $item['routes'] ?? [], true)) {
                return $key;
            }
        }

        return null;
    }
}
