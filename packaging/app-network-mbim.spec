Name: app-network-mbim
Epoch: 1
Version: 1.0.0
Release: 1%{dist}
Summary: Network MBIM
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-network

%description
The MBIM Network application provides the ability to configure and manage modems that support the MBIM protocol.

%package core
Summary: Network MBIM - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-network-core >= 1:2.4.2
Requires: libmbim-utils

%description core
The MBIM Network application provides the ability to configure and manage modems that support the MBIM protocol.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/network_mbim
mkdir -p -m 755 %{buildroot}/usr/lib/systemd/system
mkdir -p -m 755 %{buildroot}/etc/sudoers.d
cp -r * %{buildroot}/usr/clearos/apps/network_mbim/
install -D -m 0644 packaging/network-mbim.php %{buildroot}/var/clearos/base/daemon/network-mbim.php
install -D -m 0644 packaging/network-mbim.service %{buildroot}/usr/lib/systemd/system/network-mbim.service
install -D -m 0644 packaging/app-network-mbim.sudo %{buildroot}/etc/sudoers.d/app-network-mbim
rm -R %{buildroot}/usr/clearos/apps/network_mbim/packaging

%post
logger -p local6.notice -t installer 'app-network-mbim - installing'

%post core
logger -p local6.notice -t installer 'app-network-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/network_mbim/deploy/install ] && /usr/clearos/apps/network_mbim/deploy/install
fi

[ -x /usr/clearos/apps/network_mbim/deploy/upgrade ] && /usr/clearos/apps/network_mbim/deploy/upgrade

exit 0

%preun
logger -p local6.notice -t installer 'app-network-mbim - uninstalling'

%preun core
logger -p local6.notice -t installer 'app-network-mbim-core - uninstalling'

%files
%defattr(-,root,root)
/usr/clearos/apps/network_mbim/controllers
/usr/clearos/apps/network_mbim/htdocs
/usr/clearos/apps/network_mbim/views

%files core
%defattr(-,root,root)
%dir /usr/clearos/apps/network_mbim
/usr/clearos/apps/network_mbim/deploy
/usr/clearos/apps/network_mbim/language
/usr/clearos/apps/network_mbim/libraries
/var/clearos/base/daemon/network-mbim.php
/usr/lib/systemd/system/network-mbim.service
/etc/sudoers.d/app-network-mbim