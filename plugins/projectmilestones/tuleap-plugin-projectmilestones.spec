Name:		tuleap-plugin-projectmilestones
Version:	@@VERSION@@
Release:	@@TULEAP_VERSION@@_@@RELEASE@@%{?dist}
BuildArch:	noarch
Summary:	A widget for milestones monitoring

Group:		Development/Tools
License:	GPLv2
URL:		https://enalean.com
Source0:	%{name}-%{version}.tar.gz

BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root
Requires:	tuleap = @@TULEAP_VERSION@@-@@RELEASE@@%{?dist}
Requires: tuleap-plugin-agiledashboard

%description
%{summary}.

%prep
%setup -q

%install
%{__rm} -rf $RPM_BUILD_ROOT

%{__install} -m 755 -d $RPM_BUILD_ROOT/%{_datadir}/tuleap/src/www/assets
%{__install} -m 755 -d $RPM_BUILD_ROOT/%{_datadir}/tuleap/plugins/projectmilestones
%{__cp} -ar include site-content templates vendor README.mkd VERSION $RPM_BUILD_ROOT/%{_datadir}/tuleap/plugins/projectmilestones
%{__cp} -ar assets $RPM_BUILD_ROOT/%{_datadir}/tuleap/src/www/assets/projectmilestones

%clean
%{__rm} -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
%{_datadir}/tuleap/plugins/projectmilestones
%{_datadir}/tuleap/src/www/assets/projectmilestones
