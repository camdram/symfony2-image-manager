<?php
namespace Hoyes\ImageManagerBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Hoyes\ImageManagerBundle\Service\Encryptor;

class RequestListener
{
    /**
     * @var \Hoyes\ImageManagerBundle\Service\Encryptor
     */
    private $encryptor;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->request->get('_sessionid') && $request->request->get('_uploadify')) {
            $request->cookies->set(session_name(), 1);
            $id = $this->decrypt($request->request->get('_sessionid'));
            session_id($id);
        }
    }

    protected function decrypt($string)
    {
        return $this->encryptor->decrypt(preg_replace('/ /', '+', $string));
    }
}