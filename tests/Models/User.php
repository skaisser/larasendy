<?php

namespace Skaisser\LaraSendy\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Skaisser\LaraSendy\Tests\Database\Factories\UserFactory;
use Skaisser\LaraSendy\Traits\SendySubscriber;

class User extends Model
{
    use HasFactory, SendySubscriber;

    protected $fillable = [
        'email',
        'name',
        'company',
        'country',
    ];

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
