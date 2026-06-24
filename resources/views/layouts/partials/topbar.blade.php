@php
    use Illuminate\Support\Facades\Gate;
    $r = \Route::current()->getAction();
    $route = (isset($r['as'])) ? $r['as'] : '';
@endphp
<div class="header navbar" style="width:100% !important">
    <div class="header-container">
        <ul class="nav-left">
            <li>
                <a class="logo" href="{{ route(ADMIN . '.dashboard') }}" style="display: inline-block; max-width: 240px; max-height: 60px; overflow: hidden;">
                    <img src="/images/{{ config('app.logo') }}" alt="" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; border-radius: 10px;">
                </a>
            </li>
            @include('layouts.partials.menu')
        </ul>
        <ul class="nav-right">
            <li class="notifications dropdown">
            
                <?php 
                    $notification = getNotifications(auth()->user()->id);
                        if($notification){ ?>
                            <span class="counter bgc-red">{{ count($notification) }}</span>
                <?php  }  ?>

                <a href="" class="dropdown-toggle no-after" data-toggle="dropdown">
                    <i class="ti-bell"></i>
                </a>

                <ul class="dropdown-menu">
                    <li class="pX-20 pY-15 bdB">
                        <i class="ti-bell pR-10"></i>
                        <span class="fsz-sm fw-600 c-grey-900">Notifications</span>
                    </li>
                        <?php 
                            if($notification){?>
                                <li>
                                    <ul class="ovY-a pos-r scrollable lis-n p-0 m-0 fsz-sm">
                                        <?php foreach ($notification->take(5) as $val) { ?>
                                            <li>
                                                <a href="{{ route('notifications.show',Hashids::encode($val->id)) }}" class='peers fxw-nw td-n pY-10 pX-20 bdB c-grey-800 cH-blue bgcH-grey-100'>
                                                    <div class="peer peer-greed">
                                                        <small class="fsz-xs pull-right"> {{ \Carbon\Carbon::parse($val->created_at)->diffForHumans() }}</small>
                                                        <span class="fw-500">{{ $val->title }}</span> <br>
                                                        <small class="c-grey-600">{{ $val->content }}</small>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php  }  ?>
                                    </ul>
                                </li>
                        <?php  }  ?>
                    </li>
                    <li class="pX-20 pY-15 ta-c bdT">
                        <span>
                            <a href="{{ route('notifications') }}" class="c-grey-600 cH-blue fsz-sm td-n">View All Notifications
                                <i class="ti-angle-right fsz-xs mL-10"></i>
                            </a>
                        </span>
                    </li>
                </ul>
            </a>
            <li class="dropdown">
                <a href="" class="dropdown-toggle no-after peers fxw-nw ai-c lh-1" data-toggle="dropdown">
                    <div class="peer mR-10">
                        @if (auth()->user()->photo != null) 
                            <img class="w-2r h-2r bdrs-50p" src="{{ asset('storage'.auth()->user()->photo) }}" alt="">
                        @else
                            <img class="w-2r h-2r bdrs-50p"  src="/images/avatar.png"  />
                        @endif
                    </div>
                    <div class="peer">
                        <span class="fsz-sm c-grey-900">{{ auth()->user()->name }}</span>
                    </div>
                </a>
                <ul class="dropdown-menu fsz-sm">
                    @if(isEmployee())
                        <li>
                            <a href="/profile" class="dropdown-item bgcH-grey-100 c-grey-700">
                                <i class="ti-user mR-10"></i>
                                <span>Change Profile</span>
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="/profile/change_profile" class="dropdown-item bgcH-grey-100 c-grey-700">
                                <i class="ti-user mR-10"></i>
                                <span>Change Profile</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="/profile/change_password" class="dropdown-item bgcH-grey-100 c-grey-700">
                            <i class="ti-lock mR-10"></i>
                            <span>Change Password</span>
                        </a>
                    </li>
                    <li role="separator" class="dropdown-divider"></li>
                    <li>
                        <a href="/logout" class="dropdown-item bgcH-grey-100 c-grey-700">
                            <i class="ti-power-off mR-10"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        
        
    </div>

</div>
