<div id="searchfield" class="panel panel-default">
    <div class="panel-heading">
        <!-- Filter bar -->
        <div class="btn-group" role="group" aria-label="...">
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
<div id="productfield" class="panel panel-default hide">
    <div class="panel-body" id="productload">
        <h1>Products</h1>
    </div>
</div>

<!-- Begin profile screen -->
<div id="profilefield" class="panel panel-default hide">
    <div class="panel-body">
        <form>
            <div class="form-group">
                <label for="inputEmail">Email</label>
                <input type="email" class="form-control" id="inputEmail" placeholder="Email">
            </div>
            <div class="form-group">
                <label for="inputPassword">Password</label>
                <input type="password" class="form-control" id="inputPassword" placeholder="Password">
            </div>
            <div class="checkbox">
                <label><input type="checkbox"> Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</div>

<!-- Begin profile screen -->
<div id="offerfield" class="panel panel-default hide">
    <div class="panel-body">
        <h1>Offers</h1>
    </div>
</div>

