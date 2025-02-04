<?php

declare(strict_types=1);

namespace MeiliSearch\Bundle\Test;

use MeiliSearch\Bundle\SearchableEntity;
use MeiliSearch\Bundle\Test\Entity\Comment;
use MeiliSearch\Bundle\Test\Entity\Image;
use MeiliSearch\Bundle\Test\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class BaseTest.
 */
class BaseTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernel();
    }

    /**
     * @param int|string|null $id
     */
    protected function createPost($id = null): Post
    {
        $post = new Post();
        $post->setTitle('Test');
        $post->setContent('Test content');

        if (null !== $id) {
            $post->setId($id);
        }

        return $post;
    }

    protected function createSearchablePost(): SearchableEntity
    {
        $post = $this->createPost(rand(100, 300));

        return new SearchableEntity(
            $this->getPrefix().'posts',
            $post,
            $this->get('doctrine')->getManager()->getClassMetadata(Post::class),
            $this->get('serializer')
        );
    }

    /**
     * @param int|string|null $id
     */
    protected function createComment($id = null): Comment
    {
        $comment = new Comment();
        $comment->setContent('Comment content');
        $comment->setPost(new Post(['title' => 'What a post!']));

        if (null !== $id) {
            $comment->setId($id);
        }

        return $comment;
    }

    /**
     * @param int|string|null $id
     */
    protected function createImage($id = null): Image
    {
        $image = new Image();

        if (null !== $id) {
            $image->setId($id);
        }

        return $image;
    }

    protected function createSearchableImage(): SearchableEntity
    {
        $image = $this->createImage(rand(100, 300));

        return new SearchableEntity(
            $this->getPrefix().'image',
            $image,
            $this->get('doctrine')->getManager()->getClassMetadata(Image::class),
            null
        );
    }

    protected function getPrefix(): string
    {
        return $this->get('search.service')->getConfiguration()['prefix'];
    }

    protected function get(string $id): ?object
    {
        return self::$kernel->getContainer()->get($id);
    }

    /**
     * @throws \Exception
     */
    protected function refreshDb(Application $application): void
    {
        $inputs = [
            new ArrayInput(
                [
                    'command' => 'doctrine:schema:drop',
                    '--full-database' => true,
                    '--force' => true,
                    '--quiet' => true,
                ]
            ),
            new ArrayInput(
                [
                    'command' => 'doctrine:schema:create',
                    '--quiet' => true,
                ]
            ),
        ];

        $application->setAutoExit(false);
        foreach ($inputs as $input) {
            $application->run($input, new ConsoleOutput());
        }
    }

    protected function getFileName(string $indexName, string $type): string
    {
        return sprintf('%s/%s.json', $indexName, $type);
    }
}
