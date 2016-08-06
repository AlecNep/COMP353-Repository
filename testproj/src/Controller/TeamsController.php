<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Entity\User;

/**
 * Teams Controller
 *
 * @property \App\Model\Table\TeamsTable $Teams
 */
class TeamsController extends AppController
{
    public function isAuthorized($user = null){
        $user = new User($user);
        $action = $this->request->params['action'];
        $pass = $this->request->params['pass'];
        switch ($action) {
            case 'add':
            case 'delete':
            case 'edit':
                return ($user->isInstructor($pass[0]) || $user->isAdmin());
                break;
            case 'index':
                if(!$user->isAdmin() && ($group = $user->getGroup($pass[0]))){
                    return $this->redirect(['action' => 'view', $group->id]);
                }
                return $user->isInstructor($pass[0]) || 
                        $user->isAdmin() ;
            case 'view':
                if($this->Teams->exists(
                        ['id' => $pass[0]])){
                    $section = $this->Teams->get($pass[0])->section_id;
                    return 
                        $user->getGroup($section) == $pass[0] || 
                        $user->isTA($section) || 
                        $user->isInstructor($section) || 
                        $user->isAdmin();
                }
                break;
            case 'setFileSizeLimit':
                $team = $this->Teams->get($pass[0]);
                return ($user->isInstructor($team->section_id) ||
                        $user->isAdmin());
                break;
        }
        return false;
    }

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Students');
        $this->loadModel('Users');
        $this->loadModel('Sections');
    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index($id = null)
    {
        $teams = $this->Teams->find('all',
                    ['contain' => ['Users', 'Sections']])
                    ->where(['section_id' => $id]);
        $this->set(compact('teams'));
        $this->set('_serialize', ['teams']);

        $section = $this->Sections->get($id, [
                'contain' => ['Courses', 'Semesters', 'Users', 'Assignments', 'Students', 'Teams']
            ]);
        $this->set('section', $section);
        $this->set('_serialize', ['section']);

        $user = new User($this->Auth->user());
        $this->set('editable', $user->isInstructor($id) || $user->isAdmin());
    }

    /**
     * View method
     *
     * @param string|null $id Team id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = new User($this->Auth->user());

        $team = $this->Teams->get($id, 
            ['contain' => 
                ['Sections', 
                'Sections.Semesters', 
                'Sections.Courses']]);
        $this->set('team', $team);
        $this->set('_serialize', ['team']);

        $students = $this->Students->find('all', [
                'conditions' => ['team_id' => $team->id , 
                                 'section_id' => $team->section_id]
                ,'contain' => ['Users']
                ]);
        $this->set(compact('students' , 'users'));
        $this->set('_serialize', ['students']);

        $section = $team->section;
        $this->set('section', $section);
        $this->set('_serialize', ['section']);


        $canEdit = $user->isInstructor($team->section_id) || $user->isAdmin();
        $this->set('canEdit', $canEdit);

        $isInGroup = $user->getGroup($team->section_id) == $id;
        $this->set('isInGroup', $isInGroup);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $team = $this->Teams->newEntity();
        if ($this->request->is('post')) {
            $team = $this->Teams->patchEntity($team, $this->request->data);
            if ($this->Teams->save($team)) {
                $this->Flash->success(__('The team has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The team could not be saved. Please, try again.'));
            }
        }
        $users = $this->Teams->Users->find('list', ['limit' => 200]);
        $sections = $this->Teams->Sections->find('list', ['limit' => 200]);
        $this->set(compact('team', 'users', 'sections'));
        $this->set('_serialize', ['team']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Team id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $team = $this->Teams->get($id, [
            'contain' => ['Sections',
                          'Sections.Courses',
                          'Sections.Semesters']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $team = $this->Teams->patchEntity($team, $this->request->data);
            if ($this->Teams->save($team)) {
                $this->Flash->success(__('The team has been saved.'));

                return $this->redirect(['action' => 'view', $team->id]);
            } else {
                $this->Flash->error(__('The team could not be saved. Please, try again.'));
            }
        }
        $users = $this->Teams->Users->find('list', ['limit' => 200]);
        $sections = $this->Teams->Sections->find('list', ['limit' => 200]);
        $this->set(compact('team', 'users', 'sections'));
        $this->set('_serialize', ['team']);

        $this->set('section', $team->section);
    }

    /**
     * Delete method
     *
     * @param string|null $id Team id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $team = $this->Teams->get($id);
        if ($this->Teams->delete($team)) {
            $this->Flash->success(__('The team has been deleted.'));
        } else {
            $this->Flash->error(__('The team could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
