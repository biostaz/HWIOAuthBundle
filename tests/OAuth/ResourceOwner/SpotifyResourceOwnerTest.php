<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

/**
 * @author Janne Savolainen <janne.savolainen@sempre.fi>
 */
final class SpotifyResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = SpotifyResourceOwner::class;
    protected string $userResponse = <<<json
{
  "birthdate": "1937-06-01",
  "country": "SE",
  "display_name": "JM Wizzler",
  "email": "email@example.com",
  "external_urls": {
    "spotify": "https://open.spotify.com/user/wizzler"
  },
  "followers" : {
    "href" : null,
    "total" : 3829
  },
  "href": "https://api.spotify.com/v1/users/wizzler",
  "id": "wizzler",
  "images": [
    {
      "height": null,
      "url": "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/t1.0-1/1970403_10152215092574354_1798272330_n.jpg",
      "width": null
    }
  ],
  "product": "premium",
  "type": "user",
  "uri": "spotify:user:wizzler"
}
json;
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'id',
        'realname' => 'display_name',
        'email' => 'email',
        'profilepicture' => 'images.0.url',
    ];

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /**
         * @var AbstractUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertEquals('wizzler', $userResponse->getUserIdentifier());
        $this->assertEquals('wizzler', $userResponse->getNickname());
        $this->assertEquals('JM Wizzler', $userResponse->getRealName());
        $this->assertEquals('email@example.com', $userResponse->getEmail());
        $this->assertEquals('https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/t1.0-1/1970403_10152215092574354_1798272330_n.jpg', $userResponse->getProfilePicture());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass(): void
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /**
         * @var CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUserIdentifier());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetAuthorizationUrl(): void
    {
        $resourceOwner = $this->createResourceOwner();

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAuthorizationUrlWithDialog(): void
    {
        $resourceOwner = $this->createResourceOwner(['show_dialog' => 'true']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&show_dialog=true',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAuthorizationUrlWithoutDialog(): void
    {
        $resourceOwner = $this->createResourceOwner(['show_dialog' => 'false']);

        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=eyJzdGF0ZSI6InJhbmRvbSJ9&redirect_uri=http%3A%2F%2Fredirect.to%2F&show_dialog=false',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }
}
