<?php
echo $this->wpEditForm()->open($this->title, $this->errors);

foreach ($this->fields as $field) {
    echo $this->wpEditRow()->open($field);
    echo $this->detectEditHelper()->render($field);
    echo $this->wpEditRow()->close($field);
}

echo $this->wpEditForm()->close();
