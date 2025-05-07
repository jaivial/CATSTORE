root@srv736989:~# sudo ss -tuln
Netid          State            Recv-Q           Send-Q                     Local Address:Port                      Peer Address:Port          Process          
udp            UNCONN           0                0                             127.0.0.54:53                             0.0.0.0:*                              
udp            UNCONN           0                0                          127.0.0.53%lo:53                             0.0.0.0:*                              
tcp            LISTEN           0                4096                       127.0.0.53%lo:53                             0.0.0.0:*                              
tcp            LISTEN           0                200                            127.0.0.1:5432                           0.0.0.0:*                              
tcp            LISTEN           0                70                             127.0.0.1:33060                          0.0.0.0:*                              
tcp            LISTEN           0                4096                          127.0.0.54:53                             0.0.0.0:*                              
tcp            LISTEN           0                511                              0.0.0.0:80                             0.0.0.0:*                              
tcp            LISTEN           0                511                              0.0.0.0:443                            0.0.0.0:*                              
tcp            LISTEN           0                151                            127.0.0.1:3306                           0.0.0.0:*                              
tcp            LISTEN           0                4096                           127.0.0.1:27017                          0.0.0.0:*                              
tcp            LISTEN           0                200                                [::1]:5432                              [::]:*                              
tcp            LISTEN           0                511                                    *:3000                                 *:*                              
tcp            LISTEN           0                511                                [::1]:4321                              [::]:*                              
tcp            LISTEN           0                4096                                   *:8080                                 *:*                              
tcp            LISTEN           0                511                                 [::]:80                                [::]:*                              
tcp            LISTEN           0                4096                                   *:22                                   *:*                              
tcp            LISTEN           0                511                                 [::]:443                               [::]:*                              
tcp            LISTEN           0                511                                    *:5002                                 *:*                              
tcp            LISTEN           0                511                                    *:5003                                 *:*                        