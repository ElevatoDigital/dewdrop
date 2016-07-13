<?php

namespace Dewdrop\Import;

use Dewdrop\Db\Table;

class DbGateway extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_import_files');
    }

    public function loadFile($id)
    {
        $row = $this->find($id);
        return new File($row->get('full_path'), $row->get('first_row_is_headers'));
    }

    public function insertFile(array $data)
    {
        return $this->getAdapter()->insert('dewdrop_import_file_records', $data);
    }
}
