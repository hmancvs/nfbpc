<?php

class SearchController extends IndexController  {
	
	function indexAction() {
    	$session = SessionWrapper::getInstance(); 
    	// $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$conn = Doctrine_Manager::connection();
		$formvalues = $this->_getAllParams();
		$userid = $session->getVar('userid');
		
		/*$user = new Member();
		$user->populate($userid);*/
		
		$q = $formvalues['searchword'];
		$html = '';
		$hasdata = false;
		
		// ) 
		# search users
		$query = "SELECT u.id FROM useraccount as u 
			WHERE
		   (u.firstname like '%".$q."%' or 
			u.lastname like '%".$q."%' or 
			u.othernames like '%".$q."%' or 
			u.email like '%".$q."%' or 
			u.username like '%".$q."%') AND u.isactive <> 2
			GROUP BY u.id
			order by u.firstname asc, u.lastname asc LIMIT 3 ";
		// debugMessage($query);
		$result = $conn->fetchAll($query);
		$count_results = count($result);
		// debugMessage($result);
		if($count_results > 0){
			$hasdata = true;
			$html .= '<div class="separator"><span>Users</span>
				<div class="allresults"><a href="'.$this->view->baseUrl('profile/list/searchterm/'.$q).'">...see more results</a></div>
			</div><ul>';
			foreach ($result as $row){
				$user = new Member();
				$user->populate($row['id']);
				
				$b_q='<b>'.$q.'</b>';
				$name= $user->getName(); $name = str_ireplace($q, $b_q, $name);
				$type = getUserType($user->getType()); $type = str_ireplace($q, $b_q, $type);
				$media = $user->getMediumPicturePath();
				$viewurl = $this->view->baseUrl('profile/view/id/'.encode($row['id']));
				$html .= '
				<li class="display_box clearfix" url="'.$viewurl.'" theid="'.$row['id'].'">
					<a href="'.$viewurl.'">
						<div class="col-md-3 padding0 centeralign clearfix" style="padding-top:8px;">
							<img src="'.$media.'" style="width:50px;" />
						</div>
						<div style="col-md-9 padding0 clearfix">
							<div class="name col-md-12 padding0">'.$name.'</div>
							<div class=" col-md-12 padding0" style="font-size:10px; margin-top:-5px;">'.$type.'</div>
						</div>
					</a>
				</li>';
			}
		}
		
		# Clients
		/* $query = "SELECT c.id FROM contact as c 
			WHERE (c.orgname like '%".$q."%' OR
				c.firstname like '%".$q."%' OR
				c.lastname like '%".$q."%' OR
				c.contactperson like '%".$q."%'
			) GROUP BY c.id
			order by IF(c.contacttype = 1, c.orgname, concat(c.firstname, ' ', c.lastname)) asc LIMIT 3 ";
		// ('.$count_results.' entries)
		$result = $conn->fetchAll($query);
		$count_results = count($result);
		// debugMessage($result);
		if($count_results > 0){
			$hasdata = true;
			$html .= '<div class="separator"><span>Business Directory</span>
				<div class="allresults"><a href="'.$this->view->baseUrl('directory/index/tab/category/id/search/searchterm/'.$q).'">...see more results</a></div>
			</div>';
			foreach ($result as $row){
				$contact = new Contact();
				$contact->populate($row['id']);
				
				$b_q='<b>'.$q.'</b>';
				$name= $contact->getName(); $name = str_ireplace($q, $b_q, $name);
				$viewurl = $this->view->baseUrl('directory/index/tab/view/id/'.encode($row['id']));
				$media = $contact->getPicturePath();
				$html .= '
				<li class="display_box clearfix" url="'.$viewurl.'" theid="'.$row['id'].'">
					<a href="'.$viewurl.'">
						<div class="col-md-3 padding0 centeralign clearfix">
							<img class="imagecontainer" src="'.$media.'" style="width:60px;" />
						</div>
						<div style="col-md-9 padding0 clearfix">
							<div class="name col-md-12 padding0">'.$name.'</div>
						</div>
					</a>
				</li>';
			}
		} */
		
		# add navigation to searchable parameters
		$result = array(
			'id' => 1,
			'users' => ''
		);
		
		# check no data is available for all areas and return no results message
		if(!$hasdata){
			$html .= '
				<li class="display_box" align="center" style="height:30px;">
					<span style="width:100%; display:block; text-align:center;">No results for <b>'.$q.'</b></span>
				</li>';
		}
		$html .= '</ul>';
		echo $html;
    }
}