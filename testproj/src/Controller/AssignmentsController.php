<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\I18n\Time;
use App\Model\Entity\User;
use finfo; 
/**
 * Assignments Controller
 *
 * @property \App\Model\Table\AssignmentsTable $Assignments
 */
class AssignmentsController extends AppController
{
    public function isAuthorized($user = null){
        $user = new User($user);
        $action = $this->request->params['action'];
        $pass = $this->request->params['pass'];
        switch ($action) {
            case 'view': 
            case 'assignment':
            case 'download':
            case 'checkUploadFile':
            case 'saveUploadFile':
            case 'index':
            case 'submit':
            case 'recover':
            case 'deletefile':
            case 'rollback':
                return true;
                break;
            case 'add':
            case 'edit':
            case 'delete':
            case 'teamassignment':

                //can see - for section if TA for section
                //can see all - if admin?
                if($pass && $user->isTA($pass[0])==true){
                    return true;
                }
                else if ($user->isAdmin()==true){
                    return true;
                }
                break;
        }
        return false;
    }
   
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Users');
        $this->loadModel('Files');
        $this->loadModel('Submissions');
        $this->loadModel('Sections');
        $this->loadModel('Students');
        $this->loadModel('Teams');


    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index($sectionid = null)
    {
        if($sectionid==null ){
            $student = $this->Students->find('all', [
                'conditions' => ['user_id' => $this->Auth->user('id')]
            ])->first();

            $sectionid = $student->section_id;
        }

        $this->paginate = [ 'contain' => ['Sections']];
        $assignments = $this->paginate($this->Assignments);

        if($sectionid){
            $assignments = $this->paginate(
                $this->Assignments->find()
                    ->where(['section_id' => $sectionid])
            );
        }

        $this->set(compact('assignments'));
        $this->set('_serialize', ['assignments']);

        $section = $this->Sections->get($sectionid, [
            'contain' => ['Courses', 'Semesters', 'Users', 'Assignments', 'Students', 'Teams']
        ]);
        $this->set('section', $section);

        $currentuser = $this->Users->get($this->Auth->user('id'),[]);

        $this->set(compact('currentuser') );
        $this->set('_serialize', ['currentuser']);


    }

    /**
     * View method
     *
     * @param string|null $id Assignment id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $assignments = $this->Assignments->find('all', [
                'conditions' => ['section_id' => $this->request->session()->read('current_student')['section_id']]
                ]);

        $this->paginate = [
            'contain' => ['Sections']
        ];
        $assignments = $this->paginate( $assignments );

       // print_r(compact('assignments'));

        $this->set(compact('assignments'));
        $this->set('_serialize', ['assignments']);
    }


    public function assignment($id = null, $showdeleted = 0)
    {
       // $session = $this->request->session();
        if($id != null)
        {
            $student = $this->Students->find('all', [
                'conditions' => ['user_id' => $this->Auth->user('id')]
            ])->first();


            $isLeader = 0 ; 
             if ($student->team_id) {
                
                $teamleader = $this->Teams->get($student->team_id, [
                'contain' => []]);

                $isLeader  = ($teamleader->leader_user_id == $student->user_id) ? 1 : 0 ;
            }

             
            $submissions = $this->getSubmissionHistory($id,$student->team_id,$showdeleted);

            $active_submission = $this->getactivesubmission($id,$student->team_id );

            $assignment = $this->Assignments->get($id, 
              ['contain' => 
              ['Sections', 
               'Sections.Semesters', 
               'Sections.Courses']]);
            $this->set('section', $assignment->section);


         
            $this->set(compact('assignment'));
            $this->set('_serialize', ['assignment']);
            
            $this->set(compact('submissions' , 'files'));
            $this->set('_serialize', ['submissions']);

            $this->set(compact('active_submission' , 'files'));
            $this->set('_serialize', ['active_submission']);

            $this->set(compact('file'));
            $this->set('_serialize', ['user']);
            $this->set('showdeleted' , $showdeleted);
            $this->set('isLeader' , $isLeader);



        }
    }
    private function getSubmissionHistory($assignmentid, $teamid, $showdeleted)
    {
             if($showdeleted==1)
             { 
                //echo Time::now();
                $onedayago = Time::now()->subHours(28);
                //echo $onedayago;

                $submissions = $this->Submissions->find('all', [
                'conditions' => [
                                    'assignment_id' => $assignmentid ,
                                    'team_id' => $teamid ,
                                    'OR' => [ 'is_deleted' => 0
                                     , 
                                    'deletion_date is NULL  or  deletion_date > ' => $onedayago ] ,
                                    
                                    
                                ],
                'contain' => ['Files'],
                'order' => ['Files.version_number' => 'DESC']
                ]);
            }
            else
            $submissions = $this->Submissions->find('all', [
                'conditions' => [
                                    'assignment_id' => $assignmentid ,
                                    'team_id' => $teamid ,
                                    'is_deleted' => 0
                                ],
                'contain' => ['Files'],
                'order' => ['Files.version_number' => 'DESC']
                ]);

        return $submissions;
    }

    private function getActiveSubmission($assignmentid,$teamid){

        $active_submission = $this->Submissions->find('all', [
            'conditions' => [
                                'assignment_id' => $assignmentid ,
                                'team_id' => $teamid,
                                'is_deleted' => 0,
                                'is_active' => 1
                            ],
            'contain' => ['Files'],
            'order' => ['Files.version_number' => 'DESC']
            ])->first();
       
       if($active_submission == null){
        $active_submission = $this->Submissions->find('all', [
            'conditions' => [
                                'assignment_id' => $assignmentid ,
                                'team_id' => $teamid ,
                                'is_deleted' => 0
                            ],
            'contain' => ['Files'],
            'order' => ['Files.version_number' => 'DESC']
            ])->first();
        }    
        return $active_submission;
    }

    public function teamassignment($sectionid=null,$assignmentid = null, $teamid = null)
    {
        if($assignmentid!=null)
        {
            $teams = $this->Teams->find('all', [
                'conditions' => ['section_id' => $sectionid
            ]]);

            $this->set(compact('teams' ));
            $this->set('_serialize', ['teams']);
        }

        if($sectionid!=null && $assignmentid != null && $teamid != null)
        {

            $assignment = $this->Assignments->get($assignmentid, 
              []);
            $this->set(compact('assignment'));
            $this->set('_serialize', ['assignment']);

             
            $submissions = $this->getSubmissionHistory($assignmentid,$teamid,1);
            
            $this->set(compact('submissions' , 'files'));
            $this->set('_serialize', ['submissions']);  

            $active_submission = $this->getactivesubmission($assignmentid,$teamid);

            $this->set(compact('active_submission' , 'files'));
            $this->set('_serialize', ['active_submission']);


        }



            $section = $this->Sections->get($sectionid, [
                'contain' => ['Courses', 'Semesters', 'Users', 'Assignments', 'Students', 'Teams']
            ]);
            $this->set('section', $section);
            $this->set('sectionid', $sectionid);
            $this->set('assignmentid', $assignmentid);
            $this->set('teamid', $teamid);
    }    

    public function submit($id =null , $file = null)
    {
       // $session = $this->request->session();
        if($id != null)
        {

            $file = $this->Files->newEntity();
            $submission = $this->Submissions->newEntity();

            $student = $this->Students->find('all', [
                'conditions' => ['user_id' => $this->Auth->user('id')]
            ])->first();

            if ($this->request->is('post'))
            {
                //file submission form post action.
                if($this->checkUploadFile($this->request->data['submission_file'])){
                    
                    $file->name           = $this->request->data['name'] ;
                    $file->version_number = $this->request->data['version_number'] . " " ;
                    $file->user_id        = $student->user_id;
                    $file->upload_date    = Time::now();
                    $file->size_bytes     = $this->request->data['submission_file']['size'];
                    $file->checksum       = md5_file($this->request->data['submission_file']['tmp_name']);
                    $file->ip_address     = $this->get_client_ip();

                    if ($this->Files->save($file)) {

                        $submission->assignment_id = $id;
                        $submission->team_id = $student->team_id;
                        $submission->file_id = $file->id;



                        if ($this->Submissions->save($submission)) {     

                            $file_name = time() . $student->user_id . $this->request->session()->id();
                            //moving uploaded file.
                            $ext = $this->saveUploadFile($this->request->data['submission_file'], $file_name); 
                            //updating filename
                            $file->file_name = $file_name . "." . $ext;
                            
                            $this->Files->save($file);             
                            $this->Flash->success(__('Your Submission has been saved.'));
                        }
                        else
                        {
                           $this->Flash->error(__('The Submission could not be saved. Please, try again.')); 
                            return $this->redirect(['action' => 'assignment', $id ]);

                        }
                        $this->Flash->success(__('Your File has been saved.'));
                        return $this->redirect(['action' => 'assignment', $id ]);
                    } else {
                        $this->Flash->error(__('The File could not be saved. Please, try again.'));
                    }
                }
            }


            $this->set(compact('file'));
            $this->set('_serialize', ['user']);
            
            $assignment = $this->Assignments->get($id, 
              ['contain' => 
              ['Sections', 
               'Sections.Semesters', 
               'Sections.Courses']]);
            $this->set('section', $assignment->section);

        }
    }

    public function recover($id =null,$assignment=null)
    {
        $submission = $this->Submissions->find('all', [
                'conditions' => [
                                    'file_id' => $id ,
                                    'is_deleted' => 1
                                ],
                'contain' => ['Files']
                ])->first();
        if($submission != null )
        {
            $this->Flash->success(__('The file has been recovered.'));  
            $submission->is_deleted = 0;
            $submission->deletion_date = null;

            $this->Submissions->save($submission);

            return $this->redirect(['action' => 'assignment' , $assignment,1 ] );
        }
        $this->Flash->error(__('Nothing was recovered'));
        return $this->redirect(['action' => 'assignment' , $assignment,0 ] );
    }

    public function rollback($file =null ,$assignment =null,$team=null)
    {
        $submissions = $this->Submissions->find('all', [
                'conditions' => [   'assignment_id' => $assignment ,
                                    'team_id' => $team ,
                                    'is_deleted' => 0,
                                    'is_active' => 1
                                ],
                'contain' => ['Files'],
                'order' => ['Files.version_number' => 'DESC']
                ]);

        foreach( $submissions as $submission){
            $submission->is_active = 0;
            $this->Submissions->save($submission);
        }


        if($file != null && $assignment != null )
        {

            $submission = $this->Submissions->find('all', [
            'conditions' => [
                                    'file_id' => $file ,
                                    'is_deleted' => 0
                                ],
                'contain' => ['Files']
            ])->first();    

            if($submission != null )
            {
                $submission->is_active = 1;
                $this->Submissions->save($submission);
                $this->Flash->success(__('The file has been rolled back.'));  }

                return $this->redirect(['action' => 'assignment' , $assignment] );
        }
        else 
             $this->Flash->error(__('Nothing was rolledback'));
        return $this->redirect(['action' => 'assignment' , $assignment] );
    }

    public function download($fileName) {
        $path = WWW_ROOT . '/file_uploads//' . $fileName;
        $this->response->file($path, array(
            'download' => true,
            'name' => $fileName,
        ));
        return $this->response;
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add($sectionid = null)
    {
        //$this->taOnly();
        if($sectionid != null)
        {
            $assignment = $this->Assignments->newEntity();
            if ($this->request->is('post')) {
                $assignment = $this->Assignments->patchEntity($assignment, $this->request->data);
                    $assignment->section_id = $sectionid;

                if ($this->Assignments->save($assignment)) {
                    $this->Flash->success(__('The assignment has been saved.'));

                    return $this->redirect(['action' => 'index',$sectionid]);
                } else {
                    $this->Flash->error(__('The assignment could not be saved. Please, try again.'));
                }
            }
            $sections = $this->Assignments->Sections->find('list', ['limit' => 200]);
            $this->set(compact('assignment', 'sections'));
            $this->set('_serialize', ['assignment']);
                
            $section = $this->Sections->get($sectionid, [
                'contain' => ['Courses', 'Semesters', 'Users', 'Assignments', 'Students', 'Teams']
            ]);
            $this->set('section', $section);
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Assignment id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null, $sectionid=null)
    {
        //$this->taOnly();
        $assignment = $this->Assignments->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $assignment = $this->Assignments->patchEntity($assignment, $this->request->data);
            if ($this->Assignments->save($assignment)) {
                $this->Flash->success(__('The assignment has been saved.'));

                return $this->redirect(['action' => 'index' , $sectionid]);
            } else {
                $this->Flash->error(__('The assignment could not be saved. Please, try again.'));
            }
        }
        $sections = $this->Assignments->Sections->find('list', ['limit' => 200]);

        $section = $this->Sections->get($sectionid, [
            'contain' => ['Courses', 'Semesters', 'Users', 'Assignments', 'Students', 'Teams']
        ]);
        $this->set('section', $section);

        $this->set(compact('assignment', 'sections'));
        $this->set('_serialize', ['assignment']);
    }

    public function delete($id = null )
    {

            // $this->request->allowMethod(['post', 'delete']);
            // $assignment = $this->Assignments->get($id);
            // if ($this->Assignments->delete($assignment)) {
            //     $this->Flash->success(__('The assignment has been deleted.'));
            // } else {
            //     $this->Flash->error(__('The assignment could not be deleted. Please, try again.'));
            // }

            // return $this->redirect(['action' => 'index']);
    }
    /**
     * Delete method
     *
     * @param string|null $id Assignment id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function deletefile($id = null , $assignment =null)
    {

        $submission = $this->Submissions->find('all', [
                        'conditions' => [
                                            'file_id' => $id ,
                                            'is_deleted' => 0
                                        ],
                        'contain' => ['Files']
                        ])->first();
                if($submission != null )
                {
                    $this->Flash->success(__('The file has been deleted.'));  
                    $submission->is_deleted = 1;
                    $submission->deletion_date = Time::now()->subHours(4);
                    $submission->is_active = 0;

                    $this->Submissions->save($submission);

                    return $this->redirect(['action' => 'assignment' , $assignment,1 ] );
                }
                $this->Flash->error(__('Nothing was deleted'));
                return $this->redirect(['action' => 'assignment' , $assignment,0 ] );

    }


    public function checkUploadFile($file){
        
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (
                !isset($file['error']) ||
                is_array($file['error'])
            ) {
                $this->Flash->error(__('Invalid parameters.')); 
                return false;
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->Flash->error(__('No file selected.')); 
                    return false;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $this->Flash->error(__('File is too big.')); 
                    return false;
                default:
                    $this->Flash->error(__('Unknown errors.')); 
                    return false;
            }

            // $newtotalsize = $file['size']+$this->Submissions->getSizeUsed($team->id);
            // $allowedsize = $team->size_limit;
            // if ($newtotalsize > $allowedsize){

            // }
            
            // You should also check filesize here. 
            if ($file['size'] > 500000000) {
                $this->Flash->error(__('File is too big.' )); 
                return false;
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $finfo->file($file['tmp_name']),
                array(
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'docx' => 'application/zip',

                ),
                true
            )) {
                $this->Flash->error(__('Invalid file format.')); 
                return false;
            }

            return true;
    }

    public function saveUploadFile($file, $file_name){


            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $ext = array_search($finfo->file($file['tmp_name']),
                array(
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'docx' => 'application/zip',

                ),
                true
            );

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.
            if (!move_uploaded_file(
                $file['tmp_name'],
                sprintf(WWW_ROOT . '/file_uploads/%s.%s',
                    $file_name,
                    $ext
                )
            )) {
                $this->Flash->error(__('Failed to move uploaded file.')); 
                return false;
            }

            return $ext;
    }

    // Function to get the client IP address
    public function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }



}
