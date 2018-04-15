<?php

namespace AD\FlatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AD\FlatBundle\Entity\Flat;
use AD\FlatBundle\Entity\FlatImage;
use Symfony\Component\HttpFoundation\JsonResponse;
class FlatController extends Controller
{
	public function listAction()
	{
		$em = $this->getDoctrine()->getManager();
		$flats = $em->getRepository('FlatBundle:Flat')->findBy(array(
				'enabled' => true
		));
		 
		 
		return $this->render('FlatBundle::list.html.twig', array(
				'menu' => 'flat',
				'flats' => $flats
		));
	}
	
	public function showAction(Flat $flat)
	{
		if($flat == null){
			return $this->redirectToRoute('pension_list');
		}
		 
		return $this->render('FlatBundle::show.html.twig', array(
				'menu' => 'flat',
				'flat' => $flat
		));
	}
    
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteImageAction(Request $request){
    	
    	$flatImageId = $request->query->get('id');
    	$em = $this->getDoctrine()->getManager();
    	$flat = $em->getRepository('FlatBundle:FlatImage')->find($flatImageId);
    	$em->remove($flat);
    	$em->flush();
    	
    	// redirect to the 'list' view of the given entity
    	return $this->redirectToRoute('easyadmin', array(
    			'action' => 'list',
    			'entity' => $request->query->get('entity'),
    	));
    	
    }
    
    
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function uploadFlatImageAction($id, Request $request){
    	 
    	$logger = $this->get('logger');
    	$em = $this->getDoctrine()->getManager();
    	
    	//Retrieving flat on which you add the file
    	
	 	$flat = $em->getRepository('FlatBundle:Flat')->find($id);
    	$uploaded_file = $request->files->get('file');
    	
    	if($request->isMethod("POST")){
    		if($uploaded_file !== null){
	    		$logger->info('File upload for Flat : '.$flat->getName());
	    		$flatImage = new FlatImage();
	    		$flatImage->setAlt(null);
	    		$flatImage->setFlat($flat);
	    		$flatImage->setImageFile($uploaded_file);
	    		$em->persist($flatImage);
	    		
	    		$flat->addImage($flatImage);
	    		
	    		$em->flush();
	    	}
    		// redirect to the 'list' view of the given entity
    		return new JsonResponse(array('success' => true));
    	}
    	 
    	// redirect to the 'list' view of the given entity
    	return new JsonResponse(array('success' => false));
    	 
    }
    
    
}
