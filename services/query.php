<table class="table" id="searchTable"
       data-searchResults="<?php echo $data['totalResults']; ?>" 
       data-totalPages="<?php echo $data['totalPages']; ?>"
       data-currentPage="<?php echo $data['page']; ?>">
    <tbody>
        <?php
        $len = count($data['items']);
        $index = 0;
        $rowCount = 3;
        $rowptr = 0;
        ?>

        <?php while ($index < $len): ?>
            <?php $rowptr = 0; ?>
            <tr>
                <?php while ($index < $len && $rowptr < $rowCount): ?>
                    <td class="col-sm-4" data-asin="<?php echo $data['items'][$index]['asin']; ?>">
                        <img src="<?php echo $data['items'][$index]['image']; ?>" alt="none"><br/>
                        <p><?php echo $data['items'][$index]['title']; ?></p>
                        <p><?php echo $data['items'][$index]['manufacturer']; ?></p>
                        <button type="button" class="btn btn-default">Track</button>
                        <?php $index++;
                        $rowptr++;
                        ?>
                    </td>
            <?php endwhile; ?>
            </tr>
<?php endwhile; ?>

    </tbody>
</table>
<?php if ($data['totalPages'] > 1): ?>
    <nav>
        <ul class="pagination">

            <?php
            $range = min([$data['totalPages'], 5]);
            $half = (int) ($range / 2);
            $leftpad = $data['page'] - $half;
            $rightpad = $data['page'] + $half;
            if ($leftpad < 0) {
                $leftpad = $data['page'] - 1;
                $rightpad = $range - $leftpad - 1;
            } else if ($rightpad > $data['totalPages']) {
                $rightpad = $data['totalPages'] - $data['page'];
                $leftpad = $range - $rightpad - 1;
            }
            ?>

            <li <?php if ($data['page'] == 0) echo "class='disabled'"; ?>>
                <a href="#" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php for ($i = $data['page'] - $leftpad; $i <= $data['page'] + $rightpad; $i++): ?>
                <li <?php if ($i == $data['page']) echo "class='active'"; ?>><a href="#"><?php echo $i; ?></a></li>
    <?php endfor; ?>


            <li <?php if ($data['page'] == $data['totalPages'] - 1) echo "class='disabled'"; ?>>
                <a href="#" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php

 endif;



