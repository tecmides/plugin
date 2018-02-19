<?php

class analysis_for_students
{

    private $students;

    public function __construct( $students )
    {
        $this->students = $students;
        $this->numStudents = count($students);

    }

    public function get_students()
    {
        return $this->students;

    }

    public function count_discouraged_students()
    {
        $count = 0;

        foreach ( $this->students as $student )
        {
            if ( count($student->matches) > 0 )
            {
                $count ++;
            }
        }

        return $count;

    }

    public function calculate_coeficient()
    {
        $max = 0;

        foreach ( $this->students as $student )
        {
            $max = count($student->matches) > $max ? count($student->matches) : $max;
        }

        if ( $max > 0 )
        {
            foreach ( $this->students as $key => $student )
            {
                $this->students[$key]->coeficient = (count($student->matches) / $max) * 100;
            }
        }

    }

    public function rank_by_coeficient()
    {
        if ( count($this->students) > 0 )
        {
            $student = $this->students[(array_keys($this->students)[0])];

            if ( isset($student->coeficient) )
            {

                function student_coeficient_cmp( $a, $b )
                {
                    return $b->coeficient - $a->coeficient;

                }

                usort($this->students, "student_coeficient_cmp");
            }
        }

        return false;

    }

}
