<?php

use App\Exceptions\InvalidParameterException;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserDoctrineModelFactory;
use App\Models\User\UserModelInterface;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Test\AuthHelper;
use Test\ModelHelper;
use Test\ProjectHelper;
use Test\UserHelper;

/**
 * Class UserDoctrineModelFactoryTest
 */
class UserDoctrineModelFactoryTest extends TestCase
{
    use AuthHelper;
    use ModelHelper;
    use ProjectHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreate(): void
    {
        $data = [
            UserModelInterface::PROPERTY_EMAIL      => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
            UserModelInterface::PROPERTY_ID         => $this->getFaker()->numberBetween(),
            UserModelInterface::PROPERTY_CREATED_AT => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_UPDATED_AT => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_DELETED_AT => $this->getFaker()->dateTime(),
        ];

        $user = $this->getUserModelFactory()->create($data);

        $this->assertEquals($data[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($data[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
        $this->assertEquals($data[UserModelInterface::PROPERTY_ID], $user->getId());
        $this->assertEquals($data[UserModelInterface::PROPERTY_CREATED_AT], $user->getCreatedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_UPDATED_AT], $user->getUpdatedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_DELETED_AT], $user->getDeletedAt());
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateOnlyWithRequiredParameters(): void
    {
        $data = [
            UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password(),
        ];

        $user = $this->getUserModelFactory()->create($data);

        $this->assertEquals($data[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($data[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidParameters(): void
    {

        //no email
        try {
            $this->getUserModelFactory()->create(
                [
                    UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
                    UserModelInterface::PROPERTY_ID         => $this->getFaker()->numberBetween(),
                    UserModelInterface::PROPERTY_CREATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_UPDATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_DELETED_AT => $this->getFaker()->dateTime(),
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty email
        try {
            $this->getUserModelFactory()->create(
                [
                    UserModelInterface::PROPERTY_EMAIL      => '',
                    UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
                    UserModelInterface::PROPERTY_ID         => $this->getFaker()->numberBetween(),
                    UserModelInterface::PROPERTY_CREATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_UPDATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_DELETED_AT => $this->getFaker()->dateTime(),
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty password
        try {
            $this->getUserModelFactory()->create(
                [
                    UserModelInterface::PROPERTY_EMAIL      => $this->getFaker()->safeEmail,
                    UserModelInterface::PROPERTY_PASSWORD   => '',
                    UserModelInterface::PROPERTY_ID         => $this->getFaker()->numberBetween(),
                    UserModelInterface::PROPERTY_CREATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_UPDATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_DELETED_AT => $this->getFaker()->dateTime(),
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid id
        try {
            $this->getUserModelFactory()->create(
                [
                    UserModelInterface::PROPERTY_EMAIL      => $this->getFaker()->safeEmail,
                    UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
                    UserModelInterface::PROPERTY_ID         => $this->getFaker()->word,
                    UserModelInterface::PROPERTY_CREATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_UPDATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_DELETED_AT => $this->getFaker()->dateTime(),
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid created at
        try {
            $this->getUserModelFactory()->create(
                [
                    UserModelInterface::PROPERTY_EMAIL      => $this->getFaker()->safeEmail,
                    UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
                    UserModelInterface::PROPERTY_ID         => $this->getFaker()->numberBetween(),
                    UserModelInterface::PROPERTY_CREATED_AT => $this->getFaker()->word,
                    UserModelInterface::PROPERTY_UPDATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_DELETED_AT => $this->getFaker()->dateTime(),
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid updated at
        try {
            $this->getUserModelFactory()->create(
                [
                    UserModelInterface::PROPERTY_EMAIL      => $this->getFaker()->safeEmail,
                    UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
                    UserModelInterface::PROPERTY_ID         => $this->getFaker()->numberBetween(),
                    UserModelInterface::PROPERTY_CREATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_UPDATED_AT => $this->getFaker()->word,
                    UserModelInterface::PROPERTY_DELETED_AT => $this->getFaker()->dateTime(),
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid deleted at
        try {
            $this->getUserModelFactory()->create(
                [
                    UserModelInterface::PROPERTY_EMAIL      => $this->getFaker()->safeEmail,
                    UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
                    UserModelInterface::PROPERTY_ID         => $this->getFaker()->numberBetween(),
                    UserModelInterface::PROPERTY_CREATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_UPDATED_AT => $this->getFaker()->dateTime(),
                    UserModelInterface::PROPERTY_DELETED_AT => $this->getFaker()->word,
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithRefreshToken(): void
    {
        $data = [
            UserModelInterface::PROPERTY_EMAIL      => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
            UserModelInterface::PROPERTY_REFRESH_TOKENS => [
                $this->createRefreshToken()
            ]
        ];

        $this->assertEquals(
            $data[UserModelInterface::PROPERTY_REFRESH_TOKENS],
            $this->getUserModelFactory()->create($data)->getRefreshTokens()->all()
        );
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithRefreshTokenWithInvalidRefreshToken(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getUserModelFactory()->create(
            [
                UserModelInterface::PROPERTY_EMAIL      => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD   => $this->getFaker()->password(),
                UserModelInterface::PROPERTY_REFRESH_TOKENS => [
                    $this->getFaker()->uuid,
                ]
            ]
        );
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithRefreshTokenWithoutRefreshTokenArray(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getUserModelFactory()->create(
            [
                UserModelInterface::PROPERTY_EMAIL          => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD       => $this->getFaker()->password(),
                UserModelInterface::PROPERTY_REFRESH_TOKENS => $this->getFaker()->uuid,
            ]
        );
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithRefreshTokenData(): void
    {
        $refreshToken = $this->createRefreshToken();

        $refreshTokenModelFactory = $this->createRefreshTokenModelFactory();
        $refreshTokenModelFactory
            ->shouldReceive('create')
            ->andReturn($refreshToken);

        $this->assertEquals(
            [$refreshToken],
            $this->getUserModelFactory()
                ->setRefreshTokenModelFactory($refreshTokenModelFactory)
                ->create(
                    [
                        UserModelInterface::PROPERTY_EMAIL          => $this->getFaker()->safeEmail,
                        UserModelInterface::PROPERTY_PASSWORD       => $this->getFaker()->password(),
                        UserModelInterface::PROPERTY_REFRESH_TOKENS => [
                            [
                                $this->getFaker()->uuid,
                            ]
                        ]
                    ]
                )->getRefreshTokens()->all()
        );
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithInvalidRefreshTokenData(): void
    {
        $refreshTokenModelFactory = $this->createRefreshTokenModelFactory();
        $refreshTokenModelFactory
            ->shouldReceive('create')
            ->andThrow(new InvalidParameterException());

        $this->expectException(InvalidParameterException::class);

        $this->getUserModelFactory()
             ->setRefreshTokenModelFactory($refreshTokenModelFactory)
             ->create(
                 [
                     UserModelInterface::PROPERTY_EMAIL          => $this->getFaker()->safeEmail,
                     UserModelInterface::PROPERTY_PASSWORD       => $this->getFaker()->password(),
                     UserModelInterface::PROPERTY_REFRESH_TOKENS => [
                         [
                             $this->getFaker()->uuid,
                         ]
                     ]
                 ]
             );
    }

    /**
     * @return void
     */
    public function testCreateWithProjects(): void
    {
        $data = [
            'email'    => $this->getFaker()->safeEmail,
            'password' => $this->getFaker()->password(),
            'projects' => [$this->createProjectModel()],
        ];

        $this->assertEquals($data['projects'], $this->getUserModelFactory()->create($data)->getProjects()->all());
    }

    /**
     * @return void
     */
    public function testCreateWithProjectsData(): void
    {
        $data = [
            'email'    => $this->getFaker()->safeEmail,
            'password' => $this->getFaker()->password(),
            'projects' => [
                [$this->getFaker()->uuid => $this->getFaker()->uuid]
            ],
        ];
        $project = $this->createProjectModel();
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectModelFactory, $project, $data['projects'][0]);

        $this->assertEquals(
            [$project],
            $this->getUserModelFactory($projectModelFactory)->create($data)->getProjects()->all()
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProjectsArray(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getUserModelFactory()->create(
            [
                'email'    => $this->getFaker()->safeEmail,
                'password' => $this->getFaker()->password(),
                'projects' => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProjects(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getUserModelFactory()->create(
            [
                'email'    => $this->getFaker()->safeEmail,
                'password' => $this->getFaker()->password(),
                'projects' => [$this->getFaker()->word],
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProjectsData(): void
    {
        $data = [
            'email'    => $this->getFaker()->safeEmail,
            'password' => $this->getFaker()->password(),
            'projects' => [
                [$this->getFaker()->uuid => $this->getFaker()->uuid]
            ],
        ];
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectModelFactory, new InvalidParameterException(), $data['projects'][0]);

        $this->expectException(InvalidParameterException::class);

        $this->getUserModelFactory($projectModelFactory)->create($data);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testFill(): void
    {
        $data = [
            UserModelInterface::PROPERTY_EMAIL                 => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD              => $this->getFaker()->password(),
            UserModelInterface::PROPERTY_ID                    => $this->getFaker()->numberBetween(),
            UserModelInterface::PROPERTY_CREATED_AT            => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_UPDATED_AT            => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_DELETED_AT            => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_REFRESH_TOKENS        => [
                $this->createRefreshToken(),
            ],
        ];

        $user = $this->getUserModelFactory()->fill($this->createUserDoctrineModel(), $data);

        $this->assertEquals($data[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($data[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
        $this->assertEquals($data[UserModelInterface::PROPERTY_ID], $user->getId());
        $this->assertEquals($data[UserModelInterface::PROPERTY_CREATED_AT], $user->getCreatedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_UPDATED_AT], $user->getUpdatedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_DELETED_AT], $user->getDeletedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_REFRESH_TOKENS], $user->getRefreshTokens()->all());
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testFillWithoutData(): void
    {
        $this->getUserModelFactory()->fill($this->createUserDoctrineModel(), []);

        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testFillWithProject(): void
    {
        $data = [
            'projects' => [$this->createProjectModel()]
        ];

        $user = $this->getUserModelFactory()->fill($this->createUserDoctrineModel(), $data);

        $this->assertEquals($data['projects'], $user->getProjects()->all());
    }

    //endregion

    //region Mocks

    /**
     * @param ProjectModelFactory|null $projectModelFactory
     *
     * @return UserDoctrineModelFactory|MockInterface
     */
    private function getUserModelFactory(ProjectModelFactory $projectModelFactory = null): UserDoctrineModelFactory
    {
        $userDoctrineModelFactory = Mockery::mock(UserDoctrineModelFactory::class);
        $userDoctrineModelFactory
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $userDoctrineModelFactory->setProjectModelFactory(
            $projectModelFactory ?: $this->createProjectModelFactory()
        );

        return $userDoctrineModelFactory;
    }

    //endregion
}
