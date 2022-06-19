### Adding field(s)
COVID data-points are now UI configurable!

	1.	Go to the COVID Dashboard settings, manage fields, then add field like normal.
		/admin/structure/covid_dashboard_data_point/settings 
	2 . Open modules/custom/covid_dashboard/Plugin/Block/CovidDashboardBlock2 and do php operations/manipulations/stuff to the new field(s) in the build() function.
		If your new field needs to be rendered (not just affecting current render items) then:

		2.1.	Add to the return[] of modules/custom/covid_dashboard/Plugin/Block/CovidDashboardBlock2::build()
		2.2.	Map to the theme(twig). Add your data to block_covid_dashboard_2[] of modules/custom/covid_dashboard/covid_dashboard.module::covid_dashboard_theme()
		2.3.	Render then theme(twig). Your data is now available to modules/custom/covid_dashboard/templates/block-covid-dashboard-2.html.twig using the return value specified in the previous step.