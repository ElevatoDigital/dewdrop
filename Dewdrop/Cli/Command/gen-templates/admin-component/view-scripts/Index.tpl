<?php echo $this->wpWrap()->open() ?>
    <h2>
        {{title}}
        <a href="<?php echo $this->adminUrl('Edit') ?>" class="add-new-h2">Add New</a>
    </h2>
    <table class="wp-list-table widefat fixed posts" cellspacing="0">
        <tbody id="the-list">
            <?php foreach ($this->rows as $row): ?>
            <?php
            $urlParams = array();
            foreach ($this->primaryKeyColumns as $primaryKeyColumn) {
                $urlParams[$primaryKeyColumn] = $row[$primaryKeyColumn];
            }
            $rowHref = $this->escapeHtmlAttr($this->adminUrl('Edit', $urlParams));

            $name         = 'Could not determine title column';
            $titleColumns = array(
                'name',
                'full_name',
                'title',
                'description',
            );
            foreach ($titleColumns as $titleColumn) {
                if (isset($row[$titleColumn])) {
                    $name = $this->escapeHtml($row[$titleColumn]);
                    break;
                }
            }
            ?>
            <tr>
                <td>
                    <a href="<?php echo $rowHref ?>"><?php echo $name ?></a>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
<?php echo $this->wpWrap()->close() ?>
