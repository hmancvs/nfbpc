<?php

class SearchController extends IndexController  {
	
	function indexAction() {
    	$session = SessionWrapper::getInstance(); 
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$conn = Doctrine_Manager::connection();
		$formvalues = $this->_getAllParams();
		$memberid = $session->getVar('userid');
		
		$q = $formvalues['searchword'];
		$html = '';
		$hasdata = false;
		
		// ) 
		# search users
		$query = "SELECT m.id FROM member as m 
			WHERE
		   (m.firstname like '%".$q."%' or 
			m.lastname like '%".$q."%' or 
			m.othername like '%".$q."%' or 
			m.displayname like '%".$q."%' or 
			m.email like '%".$q."%' or 
			m.phone like '%".$q."%' or 
			m.username like '%".$q."%') 
			GROUP BY m.id
			order by m.displayname asc LIMIT 5 ";
		// debugMessage($query);
		$result = $conn->fetchAll($query);
		$count_results = count($result);
		// debugMessage($result);
		if($count_results > 0){
			$hasdata = true;
			$html .= '<div class="separator"><span>Members</span>
				<div class="allresults"><a href="'.$this->view->baseUrl('member/list/searchterm/'.$q).'" class="blockanchor">...see more results</a></div>
			</div><ul>';
			foreach ($result as $row){
				$member = new Member();
				$member->populate($row['id']);
				
				$b_q='<b>'.$q.'</b>';
				$name= $member->getDisplayName(); $name = str_ireplace($q, $b_q, $name);
				$phone = $member->getPhone(); $phone = str_ireplace($q, $b_q, $phone);
				$email = $member->getEmail(); $email = str_ireplace($q, $b_q, $email);
				$church = $member->getOrganisation()->getName();
				$media = $member->getMediumPicturePath();
				$viewurl = $this->view->baseUrl('member/view/id/'.encode($row['id']));
				$html .= '
				<li style="height:auto; min-height:90px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
					<img class="imagecontainer" src="'.$media.'" style="width:70px; height:auto; float:left; margin-right:6px;" />
					<div style="margin-left: 70px;">
						<span class="name blocked">'.$name.'</span>
						<span class="name blocked">'.$church.'</span>
						<span class="blocked" style="margin-top:5px;">Phone: '.$phone.'</span>
						<span class="blocked">Email: '.$email.'</span>
					</div>
				</li>';
				
			}
		}
		
		# Churches
		$query = "SELECT o.id FROM organisation as o 
			WHERE
		   (o.name like '%".$q."%' or 
			o.phone like '%".$q."%' or 
			o.email like '%".$q."%') 
			GROUP BY o.id
			order by o.name asc LIMIT 5 ";
		// debugMessage($query);
		$result = $conn->fetchAll($query);
		$count_results = count($result);
		// debugMessage($result);
		if($count_results > 0){
			$hasdata = true;
			$html .= '<div class="separator"><span>Churches</span>
				<div class="allresults"><a href="'.$this->view->baseUrl('organisation/list/searchterm/'.$q).'" class="blockanchor">...see more results</a></div>
			</div><ul>';
			foreach ($result as $row){
				$org = new Organisation();
				$org->populate($row['id']);
				
				$b_q='<b>'.$q.'</b>';
				$name= $org->getName(); $name = str_ireplace($q, $b_q, $name);
				$phone = $org->getPhone(); $phone = str_ireplace($q, $b_q, $phone);
				$email = $org->getEmail(); $email = str_ireplace($q, $b_q, $email);
				$media = $org->getMediumPicturePath();
				$viewurl = $this->view->baseUrl('organisation/view/id/'.encode($row['id']));
				$html .= '
				<li style="height:auto; min-height:90px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
					<img class="imagecontainer" src="'.$media.'" style="width:85px; height:auto; float:left; margin-right:6px; display:inline-block;" />
					<div style="margin-left: 95px;">
						<span class="name blocked">'.$name.'</span>
						<span class="blocked" style="margin-top:5px;">Phone: '.$phone.'</span>
						<span class="blocked">Email: '.$email.'</span>
					</div>
				</li>';
				
			}
		}
		
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