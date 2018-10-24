<?php

namespace tecmides\models;

class analysis_tree_tecmides_model
{
    private $students;
    private $data;

    public function __construct($students, $data )
    {
        $this->students = $students;
        $this->data = $data;
        
        foreach(array_keys($students) as $userid)
        {
            foreach($data as $classification)
            {
                if($classification->userid == $userid)
                {
                    $this->students[$userid]->approved = $classification->approved;
                }
            }
        }
    }

    public function count_approved_students()
    {
        $count = 0;
        
        foreach($this->data as $classification)
        {
            if($classification->approved == "yes")
            {
                $count++;
            }
        }
        
        return $count;
    }
    
    public function count_not_approved_students()
    {
        $count = 0;
        
        foreach($this->data as $classification)
        {
            if($classification->approved == "no")
            {
                $count++;
            }
        }
        
        return $count;
    }

}
