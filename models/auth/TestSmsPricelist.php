<?php
namespace app\models\auth;

class TestSmsPricelist extends TestPricelist
{
    public static function tableName()
    {
        return 'auth.a2p_test_pricelist';
    }

    /** Если в базовой модели есть статический фабричный метод create(), оставляем его поведение */
    public static function create(): self
    {
        return new static();
    }
}
