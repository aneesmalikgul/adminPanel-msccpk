<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">
  <!-- Brand Logo Light -->
  <a href="index.php" class="logo logo-light">
    <span class="logo-lg">
      <img src="assets/images/logo-latest-white-stroke.png" alt="logo" />
    </span>
    <span class="logo-sm">
      <img src="assets/images/logo-latest-white-stroke.png" alt="small logo" />
    </span>
  </a>

  <!-- Brand Logo Dark -->
  <a href="index.php" class="logo logo-dark">
    <span class="logo-lg">
      <img src="assets/images/logo-latest-white-stroke.png" alt="dark logo" />
    </span>
    <span class="logo-sm">
      <img src="assets/images/logo-latest-white-stroke.png" alt="small logo" />
    </span>
  </a>

  <!-- Sidebar Hover Menu Toggle Button -->
  <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
    <i class="ri-checkbox-blank-circle-line align-middle"></i>
  </div>

  <!-- Full Sidebar Menu Close Button -->
  <div class="button-close-fullsidebar">
    <i class="ri-close-fill align-middle"></i>
  </div>

  <!-- Sidebar -left -->
  <div class="h-100" id="leftside-menu-container" data-simplebar>
    <!-- Leftbar User -->
    <div class="leftbar-user">
      <a href="pages-profile.php">
        <img src="assets/images/users/avatar-1.jpg" alt="user-image" height="42" class="rounded-circle shadow-sm" />
        <span class="leftbar-user-name mt-2"><?php echo $_SESSION["first_name"] . " " . $_SESSION["last_name"]; ?></span>
      </a>
    </div>

    <!--- Sidemenu -->
    <ul class="side-nav">
      <li class="side-nav-title">Navigation</li>

      <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarDashboards" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
          <i class="ri-home-4-line"></i>
          <!-- <span class="badge bg-success float-end">2</span> -->
          <span> Dashboards </span>
        </a>
        <div class="collapse" id="sidebarDashboards">
          <ul class="side-nav-second-level">
            <!-- <li>
              <a href="dashboard-analytics.php">Analytics</a>
            </li> -->
            <li>
              <a href="index.php">Main Dashboard</a>
            </li>
          </ul>
        </div>
      </li>
      <?php if (hasPermission('view_inquiry')): ?>
        <li class="side-nav-title">Inquiries</li>
        <li class="side-nav-item">
          <a data-bs-toggle="collapse" href="#sidebarIcons" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
            <i class="ri-shopping-basket-line"></i>
            <span> Inquiries </span>
            <span class="menu-arrow"></span>
          </a>
          <div class="collapse" id="sidebarIcons">
            <ul class="side-nav-second-level">
              <li>
                <a href="drawing-inquiries.php">Drawing Inquiries</a>
              </li>
              <li>
                <a href="qoute-inquiries.php">Qoute Inquiries</a>
              </li>
            </ul>
          </div>
        </li>

      <?php endif; ?>

      <?php if (hasPermission('manage_material_rate')): ?>
        <li class="side-nav-title">Rates</li>
        <li class="side-nav-item">
          <a data-bs-toggle="collapse" href="#sidebarRates" aria-expanded="false" aria-controls="sidebarRates" class="side-nav-link">
            <i class=" ri-money-dollar-circle-line"></i>
            <span> Material Rates </span>
            <span class="menu-arrow"></span>
          </a>
          <div class="collapse" id="sidebarRates">
            <ul class="side-nav-second-level">
              <li>
                <a href="material-rates.php">Materials & Rates</a>
              </li>
            </ul>
          </div>
        </li>
      <?php endif; ?>

      <?php if (hasPermission('manage_blog')): ?>
        <li class="side-nav-title">Blogs</li>

        <li class="side-nav-item">
          <a data-bs-toggle="collapse" href="#sidebarBlogs" aria-expanded="false" aria-controls="sidebarBlogs" class="side-nav-link">
            <i class="ri-file-text-line"></i>
            <span> Blog Posts </span>
            <span class="menu-arrow"></span>
          </a>
          <div class="collapse" id="sidebarBlogs">
            <ul class="side-nav-second-level">
              <?php if (hasPermission('create_blog')): ?>
                <li>
                  <a href="blog-posts.php">Blog</a>
                </li>
              <?php endif; ?>
              <?php if (hasPermission('view_blog')): ?>
                <li>
                  <a href="all-blogs.php">All Blog</a>
                </li>
              <?php endif; ?>
              <?php if (hasPermission('manage_comments')): ?>
                <li>
                  <a href="blog-comments.php">Comments</a>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        </li>
      <?php endif; ?>


      <li class="side-nav-title">Projects</li>

      <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarProjects" aria-expanded="false" aria-controls="sidebarProjects" class="side-nav-link">
          <i class="ri-file-text-line"></i>
          <span> Projects </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarProjects">
          <ul class="side-nav-second-level">
            <li>
              <a href="projects.php">Projects</a>
            </li>
            <!-- <li>
              <a href="all-Projects.php">All Blog</a>
            </li> -->
          </ul>
        </div>
      </li>
      <li class="side-nav-title">Attendance Management</li>

      <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarProjects" aria-expanded="false" aria-controls="sidebarProjects" class="side-nav-link">
          <i class="ri-file-text-line"></i>
          <span>Attendance </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarProjects">
          <ul class="side-nav-second-level">
            <li>
              <a href="mark-attendance.php">Mark Attendance</a>
            </li>
            <!-- <li>
              <a href="all-Projects.php">All Blog</a>
            </li> -->
          </ul>
        </div>
      </li>

      <li class="side-nav-title">Tasks Management</li>

      <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarTaskManagement" aria-expanded="false" aria-controls="sidebarTaskManagement" class="side-nav-link">
          <i class="ri-file-text-line"></i>
          <span> Task Management </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarTaskManagement">
          <ul class="side-nav-second-level">
            <li>
              <a href="manage-tasks.php">Tasks</a>
            </li>
            <!-- <li>
              <a href="all-Projects.php">All Blog</a>
            </li> -->
          </ul>
        </div>
      </li>

      <li class="side-nav-title">User Management</li>

      <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarUserManagement" aria-expanded="false" aria-controls="sidebarUserManagement" class="side-nav-link">
          <i class="ri-user-line"></i>
          <span> User Management </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarUserManagement">
          <ul class="side-nav-second-level">
            <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarUserRoles" aria-expanded="false" aria-controls="sidebarUserRoles">
                <span> User Roles </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarUserRoles">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="user-roles.php">Roles</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarUser" aria-expanded="false" aria-controls="sidebarUser">
                <span> Manage Users </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarUser">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="manage-users.php">Users</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarUserPermissions" aria-expanded="false" aria-controls="sidebarUserPermissions">
                <span> User Permissions </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarUserPermissions">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="user-permissions.php">Permissions</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarAssignPermissions" aria-expanded="false" aria-controls="sidebarAssignPermissions">
                <span> Assign Permissions </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarAssignPermissions">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="assign-permissions.php">Assign Permissions</a>
                  </li>
                </ul>
              </div>
            </li>
            <!-- <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarThirdLevel" aria-expanded="false" aria-controls="sidebarThirdLevel">
                <span> Third Level </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarThirdLevel">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="javascript: void(0);">Item 1</a>
                  </li>
                  <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarFourthLevel" aria-expanded="false" aria-controls="sidebarFourthLevel">
                      <span> Item 2 </span>
                      <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarFourthLevel">
                      <ul class="side-nav-forth-level">
                        <li>
                          <a href="javascript: void(0);">Item 2.1</a>
                        </li>
                        <li>
                          <a href="javascript: void(0);">Item 2.2</a>
                        </li>
                      </ul>
                    </div>
                  </li>
                </ul>
              </div>
            </li> -->
          </ul>
        </div>
      </li>

      <!-- <li class="side-nav-title">Apps</li>

      <li class="side-nav-item">
        <a href="apps-calendar.php" class="side-nav-link">
          <i class="ri-calendar-event-line"></i>
          <span> Calendar </span>
        </a>
      </li> -->

      <!-- <li class="side-nav-item">
        <a href="apps-chat.php" class="side-nav-link">
          <i class="ri-message-3-line"></i>
          <span> Chat </span>
        </a>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarEmail" aria-expanded="false" aria-controls="sidebarEmail" class="side-nav-link">
          <i class="ri-mail-line"></i>
          <span> Email </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarEmail">
          <ul class="side-nav-second-level">
            <li>
              <a href="apps-email-inbox.php">Inbox</a>
            </li>
            <li>
              <a href="apps-email-read.php">Read Email</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarTasks" aria-expanded="false" aria-controls="sidebarTasks" class="side-nav-link">
          <i class="ri-task-line"></i>
          <span> Tasks </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarTasks">
          <ul class="side-nav-second-level">
            <li>
              <a href="apps-tasks.php">List</a>
            </li>
            <li>
              <a href="apps-tasks-details.php">Details</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a href="apps-kanban.php" class="side-nav-link">
          <i class="ri-list-check-3"></i>
          <span> Kanban Board </span>
        </a>
      </li> -->

      <!-- <li class="side-nav-item">
        <a href="apps-file-manager.php" class="side-nav-link">
          <i class="ri-folder-2-line"></i>
          <span> File Manager </span>
        </a>
      </li> -->

      <!-- <li class="side-nav-title">Custom</li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarPages" aria-expanded="false" aria-controls="sidebarPages" class="side-nav-link">
          <i class="ri-pages-line"></i>
          <span> Pages </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarPages">
          <ul class="side-nav-second-level">
            <li>
              <a href="pages-profile.php">Profile</a>
            </li>
            <li>
              <a href="pages-invoice.php">Invoice</a>
            </li>
            <li>
              <a href="pages-faq.php">FAQ</a>
            </li>
            <li>
              <a href="pages-pricing.php">Pricing</a>
            </li>
            <li>
              <a href="pages-maintenance.php">Maintenance</a>
            </li>
            <li>
              <a href="pages-starter.php">Starter Page</a>
            </li>
            <li>
              <a href="pages-preloader.php">With Preloader</a>
            </li>
            <li>
              <a href="pages-timeline.php">Timeline</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarPagesAuth" aria-expanded="false" aria-controls="sidebarPagesAuth" class="side-nav-link">
          <i class="ri-shield-user-line"></i>
          <span> Auth Pages </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarPagesAuth">
          <ul class="side-nav-second-level">
            <li>
              <a href="auth-login.php">Login</a>
            </li>
            <li>
              <a href="auth-login-2.php">Login 2</a>
            </li>
            <li>
              <a href="auth-register.php">Register</a>
            </li>
            <li>
              <a href="auth-register-2.php">Register 2</a>
            </li>
            <li>
              <a href="auth-logout.php">Logout</a>
            </li>
            <li>
              <a href="auth-logout-2.php">Logout 2</a>
            </li>
            <li>
              <a href="auth-recoverpw.php">Recover Password</a>
            </li>
            <li>
              <a href="auth-recoverpw-2.php">Recover Password 2</a>
            </li>
            <li>
              <a href="auth-lock-screen.php">Lock Screen</a>
            </li>
            <li>
              <a href="auth-lock-screen-2.php">Lock Screen 2</a>
            </li>
            <li>
              <a href="auth-confirm-mail.php">Confirm Mail</a>
            </li>
            <li>
              <a href="auth-confirm-mail-2.php">Confirm Mail 2</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarPagesError" aria-expanded="false" aria-controls="sidebarPagesError" class="side-nav-link">
          <i class="ri-error-warning-line"></i>
          <span> Error Pages </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarPagesError">
          <ul class="side-nav-second-level">
            <li>
              <a href="error-404.php">Error 404</a>
            </li>
            <li>
              <a href="error-404-alt.php">Error 404-alt</a>
            </li>
            <li>
              <a href="error-500.php">Error 500</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarLayouts" aria-expanded="false" aria-controls="sidebarLayouts" class="side-nav-link">
          <i class="ri-layout-line"></i>
          <span> Layouts </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarLayouts">
          <ul class="side-nav-second-level">
            <li>
              <a href="layouts-horizontal.php" target="_blank">Horizontal</a>
            </li>
            <li>
              <a href="layouts-detached.php" target="_blank">Detached</a>
            </li>
            <li>
              <a href="layouts-full.php" target="_blank">Full View</a>
            </li>
            <li>
              <a href="layouts-fullscreen.php" target="_blank">Fullscreen View</a>
            </li>
            <li>
              <a href="layouts-hover.php" target="_blank">Hover Menu</a>
            </li>
            <li>
              <a href="layouts-compact.php" target="_blank">Compact</a>
            </li>
            <li>
              <a href="layouts-icon-view.php" target="_blank">Icon View</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-title">Components</li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarBaseUI" aria-expanded="false" aria-controls="sidebarBaseUI" class="side-nav-link">
          <i class="ri-briefcase-line"></i>
          <span> Base UI </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarBaseUI">
          <ul class="side-nav-second-level">
            <li>
              <a href="ui-accordions.php">Accordions</a>
            </li>
            <li>
              <a href="ui-alerts.php">Alerts</a>
            </li>
            <li>
              <a href="ui-avatars.php">Avatars</a>
            </li>
            <li>
              <a href="ui-badges.php">Badges</a>
            </li>
            <li>
              <a href="ui-breadcrumb.php">Breadcrumb</a>
            </li>
            <li>
              <a href="ui-buttons.php">Buttons</a>
            </li>
            <li>
              <a href="ui-cards.php">Cards</a>
            </li>
            <li>
              <a href="ui-carousel.php">Carousel</a>
            </li>
            <li>
              <a href="ui-collapse.php">Collapse</a>
            </li>
            <li>
              <a href="ui-dropdowns.php">Dropdowns</a>
            </li>
            <li>
              <a href="ui-embed-video.php">Embed Video</a>
            </li>
            <li>
              <a href="ui-grid.php">Grid</a>
            </li>
            <li>
              <a href="ui-links.php">Links</a>
            </li>
            <li>
              <a href="ui-list-group.php">List Group</a>
            </li>
            <li>
              <a href="ui-modals.php">Modals</a>
            </li>
            <li>
              <a href="ui-notifications.php">Notifications</a>
            </li>
            <li>
              <a href="ui-offcanvas.php">Offcanvas</a>
            </li>
            <li>
              <a href="ui-placeholders.php">Placeholders</a>
            </li>
            <li>
              <a href="ui-pagination.php">Pagination</a>
            </li>
            <li>
              <a href="ui-popovers.php">Popovers</a>
            </li>
            <li>
              <a href="ui-progress.php">Progress</a>
            </li>
            <li>
              <a href="ui-spinners.php">Spinners</a>
            </li>
            <li>
              <a href="ui-tabs.php">Tabs</a>
            </li>
            <li>
              <a href="ui-tooltips.php">Tooltips</a>
            </li>
            <li>
              <a href="ui-typography.php">Typography</a>
            </li>
            <li>
              <a href="ui-utilities.php">Utilities</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarExtendedUI" aria-expanded="false" aria-controls="sidebarExtendedUI" class="side-nav-link">
          <i class="ri-stack-line"></i>
          <span> Extended UI </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarExtendedUI">
          <ul class="side-nav-second-level">
            <li>
              <a href="extended-dragula.php">Dragula</a>
            </li>
            <li>
              <a href="extended-range-slider.php">Range Slider</a>
            </li>
            <li>
              <a href="extended-ratings.php">Ratings</a>
            </li>
            <li>
              <a href="extended-scrollbar.php">Scrollbar</a>
            </li>
            <li>
              <a href="extended-scrollspy.php">Scrollspy</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a href="widgets.php" class="side-nav-link">
          <i class="ri-pencil-ruler-2-line"></i>
          <span> Widgets </span>
        </a>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarIcons" aria-expanded="false" aria-controls="sidebarIcons" class="side-nav-link">
          <i class="ri-service-line"></i>
          <span> Icons </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarIcons">
          <ul class="side-nav-second-level">
            <li>
              <a href="icons-remixicons.php">Remix Icons</a>
            </li>
            <li>
              <a href="icons-bootstrap.php">Bootstrap Icons</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarCharts" aria-expanded="false" aria-controls="sidebarCharts" class="side-nav-link">
          <i class="ri-bar-chart-line"></i>
          <span> Charts </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarCharts">
          <ul class="side-nav-second-level">
            <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarApexCharts" aria-expanded="false" aria-controls="sidebarApexCharts">
                <span> Apex Charts </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarApexCharts">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="charts-apex-area.php">Area</a>
                  </li>
                  <li>
                    <a href="charts-apex-bar.php">Bar</a>
                  </li>
                  <li>
                    <a href="charts-apex-bubble.php">Bubble</a>
                  </li>
                  <li>
                    <a href="charts-apex-candlestick.php">Candlestick</a>
                  </li>
                  <li>
                    <a href="charts-apex-column.php">Column</a>
                  </li>
                  <li>
                    <a href="charts-apex-heatmap.php">Heatmap</a>
                  </li>
                  <li>
                    <a href="charts-apex-line.php">Line</a>
                  </li>
                  <li>
                    <a href="charts-apex-mixed.php">Mixed</a>
                  </li>
                  <li>
                    <a href="charts-apex-timeline.php">Timeline</a>
                  </li>
                  <li>
                    <a href="charts-apex-boxplot.php">Boxplot</a>
                  </li>
                  <li>
                    <a href="charts-apex-treemap.php">Treemap</a>
                  </li>
                  <li>
                    <a href="charts-apex-pie.php">Pie</a>
                  </li>
                  <li>
                    <a href="charts-apex-radar.php">Radar</a>
                  </li>
                  <li>
                    <a href="charts-apex-radialbar.php">RadialBar</a>
                  </li>
                  <li>
                    <a href="charts-apex-scatter.php">Scatter</a>
                  </li>
                  <li>
                    <a href="charts-apex-polar-area.php">Polar Area</a>
                  </li>
                  <li>
                    <a href="charts-apex-sparklines.php">Sparklines</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarChartJSCharts" aria-expanded="false" aria-controls="sidebarChartJSCharts">
                <span> ChartJS </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarChartJSCharts">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="charts-chartjs-area.php">Area</a>
                  </li>
                  <li>
                    <a href="charts-chartjs-bar.php">Bar</a>
                  </li>
                  <li>
                    <a href="charts-chartjs-line.php">Line</a>
                  </li>
                  <li>
                    <a href="charts-chartjs-other.php">Other</a>
                  </li>
                </ul>
              </div>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarForms" aria-expanded="false" aria-controls="sidebarForms" class="side-nav-link">
          <i class="ri-survey-line"></i>
          <span> Forms </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarForms">
          <ul class="side-nav-second-level">
            <li>
              <a href="form-elements.php">Basic Elements</a>
            </li>
            <li>
              <a href="form-advanced.php">Form Advanced</a>
            </li>
            <li>
              <a href="form-validation.php">Validation</a>
            </li>
            <li>
              <a href="form-wizard.php">Wizard</a>
            </li>
            <li>
              <a href="form-fileuploads.php">File Uploads</a>
            </li>
            <li>
              <a href="form-editors.php">Editors</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarTables" aria-expanded="false" aria-controls="sidebarTables" class="side-nav-link">
          <i class="ri-table-line"></i>
          <span> Tables </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarTables">
          <ul class="side-nav-second-level">
            <li>
              <a href="tables-basic.php">Basic Tables</a>
            </li>
            <li>
              <a href="tables-datatable.php">Data Tables</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarMaps" aria-expanded="false" aria-controls="sidebarMaps" class="side-nav-link">
          <i class="ri-treasure-map-line"></i>
          <span> Maps </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarMaps">
          <ul class="side-nav-second-level">
            <li>
              <a href="maps-google.php">Google Maps</a>
            </li>
            <li>
              <a href="maps-vector.php">Vector Maps</a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- <li class="side-nav-item">
        <a data-bs-toggle="collapse" href="#sidebarMultiLevel" aria-expanded="false" aria-controls="sidebarMultiLevel" class="side-nav-link">
          <i class="ri-share-line"></i>
          <span> Multi Level </span>
          <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarMultiLevel">
          <ul class="side-nav-second-level">
            <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarSecondLevel" aria-expanded="false" aria-controls="sidebarSecondLevel">
                <span> Second Level </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarSecondLevel">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="javascript: void(0);">Item 1</a>
                  </li>
                  <li>
                    <a href="javascript: void(0);">Item 2</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="side-nav-item">
              <a data-bs-toggle="collapse" href="#sidebarThirdLevel" aria-expanded="false" aria-controls="sidebarThirdLevel">
                <span> Third Level </span>
                <span class="menu-arrow"></span>
              </a>
              <div class="collapse" id="sidebarThirdLevel">
                <ul class="side-nav-third-level">
                  <li>
                    <a href="javascript: void(0);">Item 1</a>
                  </li>
                  <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarFourthLevel" aria-expanded="false" aria-controls="sidebarFourthLevel">
                      <span> Item 2 </span>
                      <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarFourthLevel">
                      <ul class="side-nav-forth-level">
                        <li>
                          <a href="javascript: void(0);">Item 2.1</a>
                        </li>
                        <li>
                          <a href="javascript: void(0);">Item 2.2</a>
                        </li>
                      </ul>
                    </div>
                  </li>
                </ul>
              </div>
            </li>
          </ul>
        </div>
      </li> -->
    </ul>
    <!--- End Sidemenu -->

    <div class="clearfix"></div>
  </div>
</div>
<!-- ========== Left Sidebar End ========== -->