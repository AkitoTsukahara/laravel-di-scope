<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Analyzer;

use DIScope\Analyzer\DependencyResolver;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// テスト用のダミークラス
interface PaymentGatewayInterface {}
class StripeGateway implements PaymentGatewayInterface {}
class OrderRepository {}
class OrderService
{
    public function __construct(
        public readonly OrderRepository $repository,
        public readonly PaymentGatewayInterface $payment,
    ) {}
}

class Database {}
class UserRepository
{
    public function __construct(public readonly Database $db) {}
}
class UserService
{
    public function __construct(public readonly UserRepository $repository) {}
}

// メソッドインジェクション用のダミークラス
class SampleRequest {}
class SampleService {}

class InvokeController
{
    public function __invoke(SampleRequest $request, SampleService $service)
    {
        // メソッドインジェクション
    }
}

class HandleCommand
{
    public function handle(UserRepository $repository, Database $db)
    {
        // handleメソッドインジェクション
    }
}

class MixedDependencyService
{
    public function __construct(public readonly Database $db) {}

    public function __invoke(SampleRequest $request)
    {
        // コンストラクタ + メソッドインジェクション
    }
}

class DependencyResolverTest extends TestCase
{
    private Container $container;
    private DependencyResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->resolver = new DependencyResolver($this->container);
    }

    #[Test]
    public function クラスの依存関係を解決できる(): void
    {
        $this->container->bind(PaymentGatewayInterface::class, StripeGateway::class);

        $node = $this->resolver->resolve(OrderService::class);

        $this->assertSame(OrderService::class, $node->className);
        $this->assertCount(2, $node->dependencies);

        $dependencyClasses = array_map(
            fn($d) => $d->className,
            $node->dependencies
        );
        $this->assertContains(OrderRepository::class, $dependencyClasses);
        $this->assertContains(StripeGateway::class, $dependencyClasses);
    }

    #[Test]
    public function 依存関係を再帰的に解決できる(): void
    {
        $node = $this->resolver->resolve(UserService::class, maxDepth: 5);

        // UserService -> UserRepository -> Database
        $this->assertSame(UserService::class, $node->className);
        $this->assertSame(0, $node->depth);

        $this->assertCount(1, $node->dependencies);
        $repoNode = $node->dependencies[0];
        $this->assertSame(UserRepository::class, $repoNode->className);
        $this->assertSame(1, $repoNode->depth);

        $this->assertCount(1, $repoNode->dependencies);
        $dbNode = $repoNode->dependencies[0];
        $this->assertSame(Database::class, $dbNode->className);
        $this->assertSame(2, $dbNode->depth);
    }

    #[Test]
    public function 最大深度を超えると再帰を停止する(): void
    {
        $node = $this->resolver->resolve(UserService::class, maxDepth: 1);

        // depth=1まで: UserService -> UserRepository（Databaseまで行かない）
        $this->assertCount(1, $node->dependencies);
        $repoNode = $node->dependencies[0];
        $this->assertEmpty($repoNode->dependencies);
    }

    #[Test]
    public function 循環依存を検出してフラグを立てる(): void
    {
        // 既に訪問済みのクラスを指定
        $node = $this->resolver->resolve(
            className: UserService::class,
            depth: 0,
            maxDepth: 5,
            visited: [UserService::class],
        );

        $this->assertTrue($node->isCircular);
    }

    #[Test]
    public function 存在しないクラスは依存なしのノードを返す(): void
    {
        $node = $this->resolver->resolve('NonExistent\ClassName');

        $this->assertSame('NonExistent\ClassName', $node->className);
        $this->assertEmpty($node->dependencies);
    }

    #[Test]
    public function __invokeメソッドの依存関係を解決できる(): void
    {
        $node = $this->resolver->resolve(InvokeController::class);

        $this->assertSame(InvokeController::class, $node->className);
        $this->assertCount(2, $node->dependencies);

        $dependencyClasses = array_map(
            fn($d) => $d->className,
            $node->dependencies
        );
        $this->assertContains(SampleRequest::class, $dependencyClasses);
        $this->assertContains(SampleService::class, $dependencyClasses);
    }

    #[Test]
    public function handleメソッドの依存関係を解決できる(): void
    {
        $node = $this->resolver->resolve(HandleCommand::class);

        $this->assertSame(HandleCommand::class, $node->className);
        $this->assertCount(2, $node->dependencies);

        $dependencyClasses = array_map(
            fn($d) => $d->className,
            $node->dependencies
        );
        $this->assertContains(UserRepository::class, $dependencyClasses);
        $this->assertContains(Database::class, $dependencyClasses);
    }

    #[Test]
    public function コンストラクタとメソッドの両方の依存を解決できる(): void
    {
        $node = $this->resolver->resolve(MixedDependencyService::class);

        $this->assertSame(MixedDependencyService::class, $node->className);
        $this->assertCount(2, $node->dependencies);

        $dependencyClasses = array_map(
            fn($d) => $d->className,
            $node->dependencies
        );
        // コンストラクタから
        $this->assertContains(Database::class, $dependencyClasses);
        // __invokeから
        $this->assertContains(SampleRequest::class, $dependencyClasses);
    }
}
