<?php $this->extend('/Common/sidebar'); ?>
<?php 
	$this->set('sidebar_title', $section->course->name.' \ '.$section->id.'<br>'.$section->semester->name); 

	$linkgroups;
	if($user->isStudent($section->id)){
		$groupurl = ['controller' => 'Teams', 
				 		   'action' => 'index',
				 		   $section->id];
		if($group = $user->getGroup($section->id)){
			$groupurl = ['controller' => 'Teams', 
				 		   'action' => 'view',
				 		   $group->id];
		}
		$linkgroups[] = [
			'title' => 'Student',
			'links' => [
				['text' => 'Course Group', 
				 'url' => $groupurl],
				['text' => 'Assignments/Projects', 
				 'url' => ['controller' => 'Assignments', 
				 		   'action' => 'index',
				 		   $section->id]]

			]];
	}

	if($user->isTA($section->id)){
		$linkgroups[] = [
			'title' => 'TA Options',
			'links' => [
				['text' => 'View groups', 
				 'url' => ['controller' => 'Teams', 
				 		   'action' => 'index',
				 		   $section->id]],
				['text' => 'Assignments/Projects', 
				 'url' => ['controller' => 'Assignments', 
				 		   'action' => 'index',
				 		   $section->id]]


			

			]];
	}

	if($user->isInstructor($section->id)){
		$linkgroups[] = [
			'title' => 'Instructor Options',
			'links' => [
				['text' => 'Manage assignments', 
				 'url' => ['controller' => 'Assignments', 
				 		   'action' => 'index',
				 		   	$section->id]],
				['text' => 'Manage groups', 
				 'url' => ['controller' => 'Teams', 
				 		   'action' => 'index',
				 		   $section->id]]
			]];
	}

	if($user->isAdmin()){
		$linkgroups[] = [
			'title' => 'Admin tools',
			'links' => [
				['text' => 'Manage Groups', 
				 'url' => ['controller' => 'Teams', 
				 		   'action' => 'index',
				 		   	$section->id]]
			]];
	}

	$this->set('linkgroups', $linkgroups);
?>

<?= $this->fetch('content'); ?>