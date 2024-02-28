# Hook Api
###### Author: dinesh@softsuave.com | STATUS: WiP | VERSION: 0.2.15-dev1

 

This documentation is about the configuration which should made on hook
api in security.yml

###Authentication By-passing
For allowing the hook events to hit the respective api without
getting authenticated, add that respective hook url in security.yml under
access_control with the role as 'IS_AUTHENTICATED_ANONYMOUSLY' like below,

#####access_control:
       - { path: "^/u/profile/kyc/kychooks", role: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: "%mm_security_channel%" }
       
    
And it is better to add that path at the begining of access_control list 
to prevent other paths to get security preference over it.
For example,

#####access_control:
       - { path: "^/u/profile/kyc/kychooks", role: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: "%mm_security_channel%" }
       
       - { path: "^/u/", role: ROLE_USER, ip: 127.0.0.1, requires_channel: "%mm_security_channel%" }
   