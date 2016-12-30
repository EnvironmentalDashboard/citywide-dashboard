# City-wide Dashboard

![Environmental Dashboard](http://104.131.103.232/oberlin/prefs/images/env_logo.png "Environmental Dashboard")

### About

> A central premise of Environmental Dashboard is that real-time display of whole-community resource use can be used to promote systems thinking and foster the development of a culture of environmental stewardship that motivates individuals to conserve resources. The goal of the “City-wide Dashboard” (CWD) components are to help citizens better understand and develop a stronger sense of connection between their personal resource use and the larger environmental implications of this resource use. The CWD will help individuals see their personal decisions as an extension of commitment to the community and to environmental stewardship. The CWD is further designed to help foster the development of social norms around pro-environmental behaviors. In addition to providing context for those living and working in monitored buildings, CWD displays serve the objectives described above for the community as a whole, regardless of whether individual viewers live in monitored buildings or not.

Read the rest of the program statement [here](#)

### Installation

To install CWD on your own you need a server with PHP, MySQL, shell access, and BuildingOS API access<sup>[1](#f1)</sup>. For CWD to recieve resource consumption data, other scripts from different repositories need to be installed<sup>[2](#f2)</sup>. `install.sh` is an interactive shell script that will install the necessary dependencies and database. Read it to understand how this app is structured. Because the [time series](https://github.com/EnvironmentalDashboard/time-series) display is built on top of the framework CWD uses, the shell script will also ask you if you want to install it as well. Once installed, the directory structure will be

/cwd - Where CWD is cloned to

/[gauges](https://github.com/EnvironmentalDashboard/gauges) - The gauges of CWD are a standalone project

/[scripts](https://github.com/EnvironmentalDashboard/scripts) - Scripts to be run by cron to collect data from Lucid

/[includes](https://github.com/EnvironmentalDashboard/includes) - Classes required by the gauges, scripts, and time series

/[time-series](https://github.com/EnvironmentalDashboard/time-series) - Where the time series display will be optionally installed

/[prefs](https://github.com/EnvironmentalDashboard/prefs) - Preferences page for managing CWD and time series

---

<a name="f1">1</a>: I'm not sure how you obtain this
<a name="f2">2</a>: If you have another mechanism of obtaining data, you could just clone this repo instead of using the install script so long as you're matching the format the database expects. Additionally, the gauges on the right hand side of CWD are maintained in a [seperate repo](https://github.com/EnvironmentalDashboard/gauges). For more information, read over the install script.