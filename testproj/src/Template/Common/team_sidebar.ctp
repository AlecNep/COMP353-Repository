<?php $this->extend('/Common/section_view_sidebar'); ?>

<?php 
  $linkgroups[] = [
      'title' => 'Team \''.$team->id.'\'',
      'links' => [
          ['text' => 'Edit Team', 
           'url' => ['controller' => 'Teams', 
                     'action' => 'edit',
                     $team->id]],
          ['text' => 'Delete Team', 
           'url' => ['controller' => 'Teams', 
                     'action' => 'delete',
                     $team->id],
           'other' => ['confirm' => __('Are you sure you want to delete Team {0}?', $team->id)]],
          ['text' => 'Add a Member', 
           'url' => ['controller' => 'Students', 
                     'action' => 'memberSelect',
                     $team->id]]
      ]];
  $this->set('linkgroups', $linkgroups);

  echo $this->fetch('content');

