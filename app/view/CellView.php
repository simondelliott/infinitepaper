<?php
class CellView extends ApplicationView {

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
        foreach ($this->all as $Cell) {
?>
        <tr>
            <td><?php echo $Cell->id ?></td>
        </tr>
<?php
        }
        ?>
    </table>
<?php
    }
}
?>