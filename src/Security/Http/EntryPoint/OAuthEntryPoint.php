<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\EntryPoint;

use ArrayIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * OAuthEntryPoint redirects the user to the appropriate login url if there is
 * only one resource owner. Otherwise the user will be redirected to a login
 * page.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
final class OAuthEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly HttpKernelInterface $kernel,
        private readonly HttpUtils $httpUtils,
        private readonly string $loginPath,
        private readonly bool $useForward = false,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if ($this->useForward) {
            $subRequest = $this->httpUtils->createRequest($request, $this->loginPath);

            /** @var ArrayIterator $iterator */
            $iterator = $request->query->getIterator();
            $subRequest->query->add($iterator->getArrayCopy());

            $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            if (200 === $response->getStatusCode()) {
                $response->headers->set('X-Status-Code', '401');
            }

            return $response;
        }

        return $this->httpUtils->createRedirectResponse($request, $this->loginPath);
    }
}
