<?
class RegisterCheck
{
    private function IsDigit($cCheck) 
    { 
        return (('0'<=$cCheck) && ($cCheck<='9')); 
    }

    private function IsAlpha($cCheck) 
    {
        return ((('a'<=$cCheck) && ($cCheck<='z')) || (('A'<=$cCheck) && ($cCheck<='Z')));
    } 

    public function Verify($p)
    {
        $strUserID = $p;
        for ($nIndex=0; $nIndex<strlen($strUserID); $nIndex++)
        {
            $cCheck = $strUserID[nIndex];
            if ( $nIndex==0 && ( $cCheck =='-' || $cCheck =='_') )
            {
                return false;
            }

            if (!($this->IsDigit($cCheck) || $this->IsAlpha($cCheck) || $cCheck=='-' || $cCheck=='_' ))
            {
                return false;
            }
        }
        return true;
    } 

    private function Verifylen($p,$l,$r)
    {
        $len=strlen($p);
        if ($len>=$l && $len<=$r)
            return true;
    }

    public function check_reg($s,$c)
    {
        if ($s['VerifyCode']!=$c)
            return false;
        if (!($this->Verifylen($s[usr],4,24) && $this->Verify($s[usr])))
        {
            return false;
        }
        if (!($this->Verifylen($s[nickname],4,24)))
        {
            return false;
        }
        if (!($this->Verifylen($s[pwd],4,24) && $this->Verify($s[pwd]) && $s[pwd]==$s[repwd]))
        {
            return false;
        }
        if (!($this->Verifylen($s[passwordtip],4,64)))
        {
            return false;
        }
        if (!($this->Verifylen($s[passwordtipans],4,64)))
        {
            return false;
        }
        if (!($this->Verifylen($s[memo],4,200)))
        {
            return false;
        }
        return true;
    }
}
?>
