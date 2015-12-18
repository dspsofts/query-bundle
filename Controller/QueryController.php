<?php

/**
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 18/12/15 10:07
 */

namespace DspSofts\QueryBundle\Controller;

use DspSofts\QueryBundle\Entity\Query;
use DspSofts\QueryBundle\Form\QueryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/query")
 */
class QueryController extends Controller
{
    /**
     * @Route("/list/{page}", name="dsp_query_query_list", defaults={"page" = 1})
     */
    public function listAction($page)
    {
        $nbPerPage = 20;

        $em = $this->getDoctrine()->getManager();
        $queryRepo = $em->getRepository('DspSoftsQueryBundle:Query');

        $queries = $queryRepo->findList($page, $nbPerPage);

        $nbPages = ceil(count($queries) / $nbPerPage);

        return $this->render('@DspSoftsQuery/Query/list.html.twig', array(
            'queries' => $queries,
            'page' => $page,
            'nbPages' => $nbPages,
        ));
    }

    /**
     * @Route("/run/{id}", name="dsp_query_query_run")
     */
    public function runAction(Query $query)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \PDO $pdo */
        $pdo = $em->getConnection();

        $req = $pdo->prepare($query->getQuery());
        $req->execute();

        $results = $req->fetchAll();

        return $this->render('@DspSoftsQuery/Query/run.html.twig', array(
            'query' => $query,
            'results' => $results,
        ));
    }

    /**
     * @Route("/add", name="dsp_query_query_add")
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = new Query();
        $form = $this->createForm(new QueryType(), $query);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($query);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'info',
                    $this->get('translator')->trans('dspsofts.query.flash.created', array('name' => $query->getName()))
                );

                return $this->redirectToRoute('dsp_query_query_list');
            }
        }

        return $this->render('@DspSoftsQuery/Query/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="dsp_query_query_edit")
     */
    public function editAction(Request $request, Query $query)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new QueryType(), $query);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($query);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'info',
                    $this->get('translator')->trans('dspsofts.query.flash.updated', array('name' => $query->getName()))
                );

                return $this->redirectToRoute('dsp_query_query_list');
            }
        }

        return $this->render('@DspSoftsQuery/Query/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
