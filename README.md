# ansible-automation
repo for automation testing

#semaphoreui code is located at https://github.com/phatblinkie/semaphore-ogs

too large to keep inside this repository. this repo is mainly for plays, html, php, and mysql stuff

-----------------------------
## **User Stories**

 - As a FSR/35T, I want to be able to see the overall status of the system so that I can quickly assess the health and stability of the system. (MVP)  
 - As a FSR/35T, I want to view the status of patches so that I can track which patches have been successfully deployed, which are pending, and which have failed. (MVP)  
 - As a FSR/35T, I want to apply monthly patches and security updates. (MVP)  
 - As a FSR/35T, I want to execute major system baseline upgrades.  
 - As a FSR/35T, I want to install and configure the system starting from bare metal factory fresh state.  
 - As a FSR/35T, I want to be able to export logs for support if needed  
  

## **Acceptance Criteria**

**Dashboard Overview:**  
The system provides a dashboard that displays the overall status of the system, including:
 - Number of systems that are up to date
 - Those that require patches
 - Those with patching errors.
 - The dashboard shows a summary of the current health of the system.  

**Task/Playbook Execution:**  
 - The system has a simple task execution preferably graphical allowing simple point and click use.  
 - The system provides clear task execution status and error messages.
