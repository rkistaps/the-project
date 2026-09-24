<?php

declare(strict_types=1);

namespace TheProject\Tests\Core\Services;

use PHPUnit\Framework\TestCase;
use TheProject\Core\Abstracts\AbstractModel;
use TheProject\Core\Services\ModelDataHydratorService;

/**
 * Models are plain data: a database row becomes a model through the hydrator alone, with no container built.
 */
final class ModelHydrationWithoutContainerTest extends TestCase
{
    public function testRowWithSnakeCaseColumnsBecomesTypedModel(): void
    {
        $article = (new ModelDataHydratorService())->hydrate(new Article(), [
            'id' => '7',
            'title' => 'Hello',
            'view_count' => '42',
            'is_published' => '1',
        ]);

        self::assertSame(7, $article->id);
        self::assertSame('Hello', $article->title);
        self::assertSame(42, $article->viewCount);
        self::assertTrue($article->isPublished);
        self::assertFalse($article->isNew());
    }

    public function testUnknownColumnIsNotAddedToTheModel(): void
    {
        $article = (new ModelDataHydratorService())->hydrate(new Article(), ['unknown_column' => 'x']);

        self::assertFalse(property_exists($article, 'unknown_column'));
        self::assertFalse(property_exists($article, 'unknownColumn'));
        self::assertTrue($article->isNew());
    }
}

final class Article extends AbstractModel
{
    public string $title;
    public int $viewCount;
    public bool $isPublished;
}
