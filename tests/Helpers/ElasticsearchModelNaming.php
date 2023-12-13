<?php

declare(strict_types=1);

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @internal
 * @covers :: ElasticsearchModelNaming
 */
class ElasticsearchModelNamingTest extends TestCase
{
    /**
     * @dataProvider modelsRightData
     * 
     * @param int $id
     * @param string $type
     * @param string $result
     */
    public function testElasticsearchModelNamingRightData(int $id, string $type, string $result): void
    {
        $this->assertSame(elasticsearchModelNaming($id, $type), $result);
    }

    /**
     * @dataProvider modelsWrongData
     * 
     * @param int $id
     * @param string $type
     * @param string $result
     */
    public function testElasticsearchModelNamingWrongData(int $id, string $type, string $result): void
    {
        $this->assertSame(elasticsearchModelNaming($id, $type), $result);
    }

    /**
     * @dataProvider modelsExceptionData
     * 
     * @param int $id
     * @param string $type
     */
    public function testElasticsearchModelNamingExceptionData (int $id, string $type)
    {
        $this->expectException(TypeError::class);
        elasticsearchModelNaming($id, $type);
    }

    public function modelsRightData(): iterable
    {
        yield [1, 'user-guides', '30692365-c871-5f15-b605-d4d4316e5ab4'];
        yield [2, 'topics', 'c45156d7-8a93-5e20-9ae9-e792a89690bc'];
        yield [3, 'faq', '73bf7c2a-017a-5447-9704-0f3d44f09f9d'];
        yield [4, 'community-help', '6f68f6d4-c4a0-5251-aac8-4dfd57439427'];
    }

    public function modelsWrongData(): iterable
    {
        yield [1, 'user-guides', 'c45156d0-8a93-5e20-9ae9-e792a8969000'];
        yield [2, 'topics', 'c45156d0-8a93-5e20-9ae9-e792a8969000'];
        yield [3, 'faq', 'c45156d0-8a93-5e20-9ae9-e792a8969000'];
        yield [4, 'community-help', 'c45156d0-8a93-5e20-9ae9-e792a8969000'];
    }

    public function modelsExceptionData(): iterable
    {
        yield [null, 'user-guides', 'null-id-value'];
        yield ['123', 'topics', 'string-id-value'];
        yield [false, 'faq', 'boolean-id-value'];
        yield [1.234, 'community-help', 'float-id-value'];

        yield [1, null, 'null-type-value'];
        yield [2, 2, 'integer-type-value'];
        yield [3, false, 'boolean-type-value'];
        yield [4, 1.234, 'float-type-value'];
    }
}
