############################################################## CAP PROVISIONING COMMANDS START

/ip dhcp-client add add-default-route=yes default-route-distance=0 dhcp-options=hostname,clientid disabled=no interface=ether1;
/system scheduler add name=auto-set-cap-on on-event="/interface wireless cap set interfaces=wlan1 certificate=request discovery-interfaces=ether1 lock-to-caps-man=no bridge=none enabled=yes" policy=ftp,read,write,policy,test,winbox,sniff,sensitive,api start-time=startup

:global lastrosver "6.24";
#:global lastwirelessver "6.24";
:global lastcapver "6.24";
:global httptorosnpk "http://10.1.1.1:81/routeros-mipsbe-6.24.npk";
#:global httptowirelessnpk "http://10.1.1.1:81/wireless-cm2-6.24-mipsbe.npk";
:global httptocapnpk "http://10.1.1.1:81/wireless-cm2-6.24-mipsbe.npk";

:local rosver [/system package get [find where name~"routeros"] version ];:if ($rosver !=$lastrosver) do={
   /tool fetch url=$httptorosnpk;
   /delay 5;
   /system reboot;
} else={:local iscap [:len [/system package find name~"wireless-cm2"]];:if ($iscap =0) do={
   /tool fetch url=$httptocapnpk;
   /delay 5;
   /system reboot;
   } else={:local capver [/system package get [find where name~"wireless-cm2"] version ];:if ($capver !=$lastcapver) do={
            /tool fetch url=$httptocapnpk;
            /delay 5;
            /system reboot;
            } else={/ip address remove [find where dynamic=no];
              /ip dhcp-client remove [find where interface!~"eth"];
              /interface bridge remove [find];
              /interface bridge port remove [find];
              /ip route remove [find where distance > 0];
              /ip dhcp-server remove [find];
              /ip pool remove [find];
              /ip dhcp-server network remove [find];
              /system script remove [find];
              /ip firewall address-list remove [find];
              /ip firewall nat remove [find];
              /ip firewall filter remove [find];
              /system scheduler remove [find];
              :local isled [:len [/system leds find]];:if ($isled=0) do={
               /system leds add leds=all-leds type=interface-activity interface=ether1 disabled=no;
               } else={/system leds set 0 interface=ether1 leds=all-leds type=interface-activity;
                 }
              }
     }
  }



############################################################## CAP PROVISIONING COMMANDS END
############################################################## CAP PROVISIONING SCRIPT OT PASTE START

/ip dhcp-client add add-default-route=yes default-route-distance=0 dhcp-options=hostname,clientid disabled=no interface=ether1;
/system scheduler
add interval=30s name=provision on-event=":global lastrosver \"6.24\";\r\
    \n:global lastcapver \"6.24\";\r\
    \n:global httptorosnpk \"http://10.1.1.1/prov/npk/routeros-mipsbe-6.25.npk\";\r\
    \n:global httptocapnpk \"http://10.1.1.1/prov/npk/wireless-cm2-6.25-mipsbe.npk\";\r\
    \n\r\
    \n:local rosver [/system package get [find where name~\"routeros\"] version ];:if (\$rosver !=\$lastrosver) do={\r\
    \n   /tool fetch url=\$httptorosnpk;\r\
    \n   /delay 5;\r\
    \n   /system reboot;\r\
    \n   } else={:local iscap [:len [/system package find name~\"wireless-cm2\"]];:if (\$iscap =0) do={\r\
    \n      /tool fetch url=\$httptocapnpk;\r\
    \n      /delay 5;\r\
    \n      /system reboot;\r\
    \n         } else={:local capver [/system package get [find where name~\"wireless-cm2\"] version ];:if (\$capver !=\$l\
    astcapver) do={\r\
    \n            /tool fetch url=\$httptocapnpk;\r\
    \n            /delay 5;\r\
    \n            /system reboot;\r\
    \n               } else={/ip address remove [find where dynamic=no];\r\
    \n               /ip dhcp-client remove [find where interface!~\"eth\"];\r\
    \n               /interface bridge remove [find];\r\
    \n               /interface bridge port remove [find];\r\
    \n               /ip route remove [find where distance>0];\r\
    \n               /ip dhcp-server remove [find];\r\
    \n               /ip pool remove [find];\r\
    \n               /ip dhcp-server network remove [find];\r\
    \n               /system script remove [find];\r\
    \n               /ip firewall address-list remove [find];\r\
    \n               /ip firewall nat remove [find];\r\
    \n               /ip firewall filter remove [find];\r\
    \n               /system scheduler remove [find];\r\
    \n               :local isled [:len [/system leds find]];:if (\$isled=0) do={\r\
    \n                  /system leds add leds=all-leds type=interface-activity interface=ether1 disabled=no;\r\
    \n                  } else={/system leds set 0 interface=ether1 leds=all-leds type=interface-activity;\r\
    \n                     }\r\
    \n                  }\r\
    \n           }\r\
    \n     }\r\
    \n" policy=ftp,reboot,read,write,policy,test,winbox,password,sniff,sensitive,api start-time=startup
add interval=10s name=set-cap-on on-event="/interface wireless cap set interfaces=wlan1 certificate=request discovery-inte\
    rfaces=ether1 lock-to-caps-man=no bridge=none enabled=yes" policy=\
    ftp,reboot,read,write,policy,test,winbox,password,sniff,sensitive,api start-time=startup
/quit

############################################################## CAP PROVISIONING SCRIPT OT PASTE END
