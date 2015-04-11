<div id="searchfield" class="panel panel-default">
    <div class="panel-heading">
        <!-- Filter bar -->
        <div class="btn-toolbar" role="group" aria-label="...">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    Condition<span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu" id="queryCond">
                    <li><a>All</a></li>
                    <li><a>New</a></li>
                    <li><a>Used</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-default">Price</button>

            <input type="hidden" value="" id="currentPage">

            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    Category<span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu" id="queryCategory">
                    <li><a>All</a></li>
                    <li><a>Beauty</a></li>
                    <li><a>Books</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Search bar -->
    <div class="panel-body">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Keywords..." id="keywords">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" id="queryFire">Go!</button>
            </span>
        </div>

        <!-- Results -->
        <div class="panel panel-primary">
            <div class="panel-heading">Results</div>
            <div class="panel-body" id="searchload">

            </div>
        </div>

    </div>
</div>

<!-- Begin products screen -->
<div id="productfield" class="panel panel-primary hide">
    <div class="panel-heading">Tracked Products</div>
    <div class="panel-body" id="productload">
            <table class="table table-bordered" id="productTable">
            <thead>
                <tr>
                    <th class="col-xs-1"></th>
                    <th class="col-xs-4">Title</th>
                    <th class="col-xs-4">Maker</th>
                    <th class="col-xs-1">ASIN</th>
                    <th class="col-xs-1">Priority</th>
                    <th class="col-xs-1"></th>
                </tr>
            </thead>
            <tbody>
            <?php  if(isset($products) && !empty($products)): ?>
                <?php  $count = 0;  ?>
                <?php foreach ($products as $value): ?>
            <tr <?php echo "id='row$count'";  ?>>
                <!-- <td class="col-xs-1"><button class="btn btn-default" type="button">delete</button></td> -->
                <!-- <a href="#con2"><span class="glyphicon glyphicon-home"></span> Google+</a> -->
                <td class="col-xs-1"><button class="btn btn-default" type="button">offers</button></td>
                <td class="col-xs-4"><p><?php echo $value['title']; ?></p></td>
                <td class="col-xs-4"><p><?php echo $value['maker']; ?></p></td>
                <td class="col-xs-1"><p><?php echo $value['asin']; ?></p></td>
                <td class="col-xs-1"><p><?php echo $value['priority']; ?></p></td>
                <td class="col-xs-1"><a href="#"><span class="glyphicon glyphicon-trash"></span></a></td>
            </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Begin profile screen -->
<div id="profilefield" class="panel panel-default hide" data-uid="<?php echo $uid; ?>">
    <div class="panel-body">
        <form>
            <div class="row">
                <div class="col-xs-6 form-group">
                    <label for="profileFirst">First</label>
                    <input type="text" class="form-control" id="profileFirst"
                        <?php if(isset($profile['firstName']) && !empty($profile['firstName'])) echo "value='{$profile['firstName']}'"; ?>>
                </div>
                <div class="col-xs-6 form-group">
                    <label for="profileLast">Last</label>
                    <input type="text" class="form-control" id="profileLast"
                           <?php if(isset($profile['lastName']) && !empty($profile['lastName'])) echo "value='{$profile['lastName']}'"; ?>>
                </div>
            </div>
            <div class="form-group">
                <label for="profileUsername">Username</label>
                <input type="text" class="form-control" id="profileUsername"
                       <?php if(isset($profile['username']) && !empty($profile['username'])) echo "value='{$profile['username']}'"; ?>>
            </div>
            <div class="form-group">
                <label for="profileEmail">Email</label>
                <input type="email" class="form-control" id="profileEmail"
                       <?php if(isset($profile['email']) && !empty($profile['email'])) echo "value='{$profile['email']}'"; ?>>
            </div>
            <div class="row">
                <div class="col-xs-6 form-group">
                    <label for="profilePhone">Phone</label>
                    <input type="text" class="form-control" id="profilePhone"
                           <?php if(isset($profile['phone']) && !empty($profile['phone'])) echo "value='{$profile['phone']}'"; ?>>
                </div>
                <div class="col-xs-6 form-group">
                    <label for="profileCarrier">Carrier</label>
                    <input type="text" class="form-control" id="profileCarrier"
                           <?php if(isset($profile['carrier']) && !empty($profile['carrier'])) echo "value='{$profile['carrier']}'"; ?>>
                </div>
            </div>
            <div class="checkbox">
                <label><input type="checkbox"> Auto-login</label>
            </div>
            <button type="button" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>

<!-- Begin profile screen -->
<div id="offerfield" class="panel panel-default hide">
    <div class="panel-body">
        <h1>Offers</h1>
    </div>
</div>

