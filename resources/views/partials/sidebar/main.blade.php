<nav class="col-md-2 sidebar">
    <div class="user-box text-center pt-5 pb-3">
        <div class="user-img">
            <img src="{{ auth()->user()->present()->avatar }}"
                 width="90"
                 height="90"
                 alt="user-img"
                 class="rounded-circle img-thumbnail img-responsive">
        </div>
        <h5 class="my-3">
            <a href="{{ route('profile') }}">{{ auth()->user()->present()->nameOrEmail }}</a>
        </h5>

        <ul class="list-inline mb-2">
            <li class="list-inline-item">
                <a href="{{ route('profile') }}" title="@lang('My Profile')">
                    <i class="fas fa-cog"></i>
                </a>
            </li>

            <li class="list-inline-item">
                <a href="{{ route('auth.logout') }}" class="text-custom" title="@lang('Logout')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            @foreach (\Vanguard\Plugins\Vanguard::availablePlugins() as $plugin)
                @include('partials.sidebar.items', ['item' => $plugin->sidebar()])
            @endforeach

            <li class="nav-item">
                <a class="nav-link " href="{{ route('fundraiser.list') }}">
                    <i class="fas fa-archive"></i>
                    <span> View Fundraisers</span>
                </a>
            </li>


            <li class="nav-item">
                <a class="nav-link " href="{{ route('get-invoices') }}">
                    <i class="fas fa-file"></i>
                    <span>Invoices</span>
                </a>
            </li>

            <li class="nav-item" style="color: #f1f1f1; margin: 100px 0; font-weight: 900;">
                <i class="fas fa-calendar-plus-o"></i>
                Date Joined

                <p style="font-weight: 100; margin-top: 20px">
                    <span> {{ auth()->user()->created_at->diffForHumans() }}</span>
                </p>
                <p style="font-weight: 100;">
                    <span> {{ auth()->user()->created_at->format('d F Y') }}</span>
                </p>
            </li>



        </ul>

    </div>
</nav>

