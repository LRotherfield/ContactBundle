<?php

namespace Rothers\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Rothers\ContactBundle\Entity\Contact;
use Rothers\ContactBundle\Form\ContactType;
use Rothers\ContactBundle\Utility\Useful;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Form\FormError;

/**
 * @Route("/contact")
 */
class ClientController extends Controller {

    /**
     * @Route("/thank-you", name="contact_thankyou")
     * @Template
     */
    public function thankyouAction() {
        return array();
    }

    /**
     * @Route("/{blank_layout}", name="new_contact", defaults={"blank_layout" = false})
     * @Template
     */
    public function contactAction($blank_layout) {
        $em = $this->getDoctrine()->getEntityManager();
        $contact = new Contact();
        $class = $this->container->getParameter('contact.bundle.type');
        $form = $this->createForm(new $class(), $contact);
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $save = true;
                if (!$this->stripper($contact->getName())) {
                    $form->addError(new FormError("You must leave your name so we know who to contact."));
                    $save = false;
                }
                if (!$this->stripper($contact->getMessage())) {
                    $form->addError(new FormError("We want to hear from you but if you leave your message blank we wont hear anything."));
                    $save = false;
                }
                if ($save) {
                    $em->persist($contact);
                    $em->flush();
                    $message = \Swift_Message::newInstance()
                            ->setSubject('Contact form')
                            ->setFrom($this->container->getParameter('contact.from.email'))
                            ->setTo($contact->getEmail())
                            ->setBody($this->renderView('ContactBundle:Email:user.html.twig', array(
                                'name' => $contact->getName())
                            ), 'text/html');
                    $this->get('mailer')->send($message);
                    $message = \Swift_Message::newInstance()
                            ->setSubject('Contact form')
                            ->setFrom($this->container->getParameter('contact.from.email'))
                            ->setTo($this->container->getParameter('contact.to.email'))
                            ->setBody($this->renderView('ContactBundle:Email:admin.html.twig', array(
                                'contact' => $contact)
                            ), 'text/html');
                    $this->get('mailer')->send($message);
                    $this->get('session')->setFlash('notice', 'Thank you for contacting us');

                    return new RedirectResponse($this->generateUrl('contact_thankyou'));
                }
            }
        }
        $params = array('form' => $form->createView(), 'blank_layout' => $blank_layout);
        if ($blank_layout)
            return $this->render('ContactBundle:Client:contact-form.html.twig', $params);
        return $this->render('ContactBundle:Client:contact.html.twig', $params);
    }

    private function stripper($val) {
        foreach (array(' ', '&nbsp;', '\n', '\t', '\r') as $strip) {
            $val = str_replace($strip, '', (string) $val);
        }
        return $val === '' ? false : $val;
    }

}