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
     * @Route("/export/{id}", name="dsp_query_query_export")
     */
    public function exportAction(Query $query)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \PDO $pdo */
        $pdo = $em->getConnection();

        $req = $pdo->prepare($query->getQuery());
        $req->execute();

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0);

        $numRow = 0;
        while ($row = $req->fetch()) {
            $numRow++;
            if ($numRow == 1) {
                $numCol = 0;
                foreach (array_keys($row) as $val) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($numCol, $numRow, $val);
                    $numCol++;
                }
                $numRow++;
            }

            $numCol = 0;
            foreach ($row as $val) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($numCol, $numRow, $val);
                $numCol++;
            }
        }
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $file = "/tmp/test.xlsx";
        $objWriter->save($file);

        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Length: ' . filesize($file));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        readfile($file);
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
