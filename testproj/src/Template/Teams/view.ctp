<?php $this->extend('/Common/section_view_sidebar'); ?>

<?php 
    if($canEdit):
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
                           $team->id]],
            ]];
        $this->set('linkgroups', $linkgroups);
    endif;
?>

<span class='bold'>Course Group</span><br>
<?php 
    if($isInGroup){
        echo 'You are currently in this group.';
    }
?>
<hr>

<h3><?= "Team " . h($team->id) ?></h3>
<table>
    Members
    <tr>
        <th>Student ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Role</th>
        <?php if($canEdit): ?>
            <th>Actions</th>
        <?php endif; ?>
    </tr>
    <?php foreach($students as $student): ?>
        <tr>
            <td><?= $student->user->id ?></td>
            <td><?= $student->user->first_name ?></td>
            <td><?= $student->user->last_name ?></td>
            <td><?= $student->user->email ?></td>
            <td><?= ($student->user->id == $team->leader_user_id)?'Leader':'-' ?></td>
            <?php if($canEdit): ?>
                <td>
                    <?= $this->Html->link('Remove from Team',
                            ['controller' => 'Students', 
                             'action' => 'clearTeam',
                             $student->user->id,
                             $team->section->id],
                             ['confirm' => __('Are you sure you want to remove \'{0} {1}\' from Team {2}?', $student->user->first_name, $student->user->last_name, $team->id)]) ?>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>
