<?php

namespace tecmides\models;

class analysis_rule_tecmides_model
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
                    $this->students[$userid]->discouraged = $classification->discouraged;
                    $this->students[$userid]->coeficient = floatval($classification->discouraged_coeficient);
                }
            }
        }
    }

    public function count_discouraged_students()
    {
        $count = 0;
        
        foreach($this->data as $classification)
        {
            if($classification->discouraged == "yes")
            {
                $count++;
            }
        }
        
        return $count;
    }
    
    public function count_not_discouraged_students()
    {
        $count = 0;
        
        foreach($this->data as $classification)
        {
            if($classification->discouraged == "no")
            {
                $count++;
            }
        }
        
        return $count;
    }

    public function get_students_ranked_by_coeficient()
    {
        $ranked = array_values($this->students);
        
        usort($ranked, array($this, "ranked_by_coeficient_cmp"));
        
        return $ranked;
    }
    
    private function ranked_by_coeficient_cmp($a, $b)
    {
        return $b->coeficient > $a->coeficient ? 1 : -1;
    }

}
