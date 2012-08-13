<?php

class CRM_Utils_IMAP {

    private $conn = NULL;

    public function __construct($server,$user,$pass) {
        $this->conn = imap_open($server, $user, $pass);
    }

    public function conn() {
        return $this->conn;
    }

    public function getmsg_uid($uid) {
        return $this->getmsg(imap_msgno($this->conn,$uid));
    }
    public function getmsg($id) {
        //Initialize our email
        $email = new stdClass();
        $email->attachments = array();
        $email->htmlmsg = '';
        $email->plainmsg = '';
        $email->charset = '';

        // HEADER
        $header = imap_header($this->conn,$id);
        $email->to = $header->to;
        $email->cc = $header->cc;
        $email->bcc = $header->bcc;
        $email->from = $header->from;
        $email->date = $header->date;
        $email->sender = $header->sender;
        $email->reply_to = $header->reply->to;
        $email->return_path = $header->return_path;
        $email->subject = $header->subject;
        $email->uid = $header->message_id;
        $email->msgno = $header->Msgno;
        $email->time = $header->udate;
        $email->in_reply_to = $header->in_reply_to;
        $email->followup_to = $header->followup_to;

        // BODY
        $s = imap_fetchstructure($this->conn,$id);
        if (!$s->parts)  // not multipart
            self::getpart($email,$id,$s,0);  // no part-number, so pass 0
        else {  // multipart: iterate through each part
            foreach ($s->parts as $partno0=>$p)
                self::getpart($email,$id,$p,$partno0+1);
        }
        return $email;
    }

    private function getpart($email,$id,$p,$partno) {
        // PART DATA
        // all but the [0] part are considered multipart
        if ($partno == 0)
            $data = imap_body($this->conn,$id);
        else
            $data = imap_fetchbody($this->conn,$id,$partno);

        // DECODING
        // Any part may be encoded, even plain text messages, so check everything.
        // no need to decode 7-bit, 8-bit, or binary
        if ($p->encoding==4)
            $data = quoted_printable_decode($data);
        elseif ($p->encoding==3)
            $data = base64_decode($data);

        // PARAMETERS
        // get all parameters, like charset, filenames of attachments, etc.
        $params = array();
        if ($p->parameters)
            foreach ($p->parameters as $x)
                $params[ strtolower( $x->attribute ) ] = $x->value;
        if ($p->dparameters)
            foreach ($p->dparameters as $x)
                $params[ strtolower( $x->attribute ) ] = $x->value;

        // ATTACHMENT
        // Any part with a filename is an attachment,
        // so an attached text file (type 0) is not mistaken as the message.
        // filename may be given as 'Filename' or 'Name' or both
        // filename may be encoded, so see imap_mime_header_decode()
        // Currently breaks with two same named attachments
        if ($params['filename'] || $params['name']) {
            $filename = ($params['filename'])? $params['filename'] : $params['name'];
            $email->attachments[$filename] = $data;
        }

        // TEXT
        // Messages may be split in different parts because of inline attachments,
        // so append parts together with blank row.
        // assume all parts are the same charset
        elseif ($p->type==0 && $data) {
            if (strtolower($p->subtype)=='plain')
                $email->plainmsg .= trim($data) ."\n\n";
            else
                $email->htmlmsg .= $data ."<br><br>";
            $email->charset = $params['charset'];
        }

        // EMBEDDED MESSAGE
        // Many bounce notifications embed the original message as type 2,
        // but AOL uses type 1 (multipart), which is not handled here.
        // There are no PHP functions to parse embedded messages,
        // so this just appends the raw source to the main message.
        elseif ($p->type==2 && $data) {
            $email->plainmsg .= trim($data) ."\n\n";
        }

        // SUBPART RECURSION
        // 1.2, 1.2.1, etc.
        if ($p->parts) {
            foreach ($p->parts as $partno0=>$p2)
                $pnum = $partno.'.'.($partno0+1);
                self::getpart($email,$id,$p2,$pnum);
        }
    }

    public function movemsg_uid($uid, $newBox) {
        return $this->movemsg(imap_msgno($this->conn,$uid), $newBox);
    }

    public function movemsg($id, $newBox) {
        $success = imap_mail_move($this->conn, $id, $newBox);
        if($success) {
            imap_expunge($this->conn);
        }
        return $success;
    }

    public function deletemsg_uid($uid) {
        return $this->deletemsg(imap_msgno($this->conn, $uid));
    }

    public function deletemsg($id) {
        $success = imap_delete($this->conn, $id);
        if($success) {
            imap_expunge($this->conn);
        }
        return $success;
    }
}