<?php

namespace App\GraphQL\Type;

use App\Entity\Client;
use Overblog\GraphQLBundle\Definition\Type\ObjectType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasResolver;

class ClientType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Client',
            'fields' => [
                'id' => ['type' => 'ID'],
                'name' => ['type' => 'String'],
                'email' => ['type' => 'String'],
                'phone' => ['type' => 'String'],
            ],
            'resolveField' => function ($value, $args, $context, \GraphQL\Type\Definition\ResolveInfo $info) {
                $method = 'get' . ucfirst($info->fieldName);
                if (method_exists($value, $method)) {
                    return $value->$method();
                }
                return null;
            }
        ]);
    }
}
