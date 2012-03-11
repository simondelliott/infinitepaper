<?php
class BaseModelView extends ApplicationView {

    private $all;

    public function __construct( $all ) {
        $this->all = $all;
    }

    public function contents () {
        ?>
    <table>
        <tr>
            <th>id</th>
        </tr>
<?php
        foreach ($this->all as $BaseModel) {
?>
        <tr>
            <td><?php echo $BaseModel->id ?></td>
        </tr>
<?php
        }
        ?>
    </table>
<?php
    }
}
?>