![Non Logged Users](https://i.ibb.co/GPGt1Qt/Nu-Lx8mcl-Qbmn-UVt-Jj-Y3-GNw.png)

Project Status: Active – The project has reached a stable, usable state and is being actively developed.
![https://www.repostatus.org/#wip](https://user-images.githubusercontent.com/76659584/211010037-f264dc21-57db-4158-b3b6-768ddf93db06.png)

#  Non Logged Users

This script used to automate the process of creating users in Okta based on tickets in Zendesk. It allows only certain approved users to approve the creation of a user, and uses custom fields on the ticket to determine the details for the user to be created in Okta. It also updates the ticket with comments indicating the status of the user creation process and, if successful, marks the ticket as solved.

# Full information
This code snippet implements a system for user registration with admin approval on a WordPress website. Here's a breakdown of its goals, functionalities, and security measures:

Goal:

Control user access to the website by requiring administrator approval for new registrations.
Functionalities:

Restricting Access for Non-Logged-in Users:

The redirect_non_logged_in_users function checks if a user is logged in or trying to access the login page or registration area.
If not, it redirects them to the login page.
Custom Login Messages:

The custom_login_message function alters the default login message.
If a user attempts to login after registration, it displays a message informing them their account is pending approval.
Otherwise, it displays a message stating only registered and logged-in users can access the site.
Registration and User Role Management:

Upon user registration (triggered by user_register action), two things happen:
notify_user_registration_pending sends an email notification to the user informing them their registration awaits admin approval.
redirect_after_registration redirects the newly registered user to the login page with a "registration=pending" parameter.
The set_pending_role_on_registration function assigns the newly registered user a "pending" role (unless they are an administrator).
User Approval and Notification:

The notify_user_when_approved function is triggered whenever a user's role changes (using set_user_role action).
If the user's role transitions from "pending" to another role (meaning they are approved), it sends an email notification congratulating them and providing the login link.
Security Measures:

Data Sanitization: The code utilizes various sanitization functions like sanitize_text_field, sanitize_email, and sanitize_textarea_field to prevent potential security vulnerabilities like XSS (Cross-Site Scripting) attacks. These functions ensure user-provided data is properly cleaned before being used in emails or stored in the database.
WordPress Core Functions: The code leverages built-in WordPress functions like wp_safe_redirect for redirection, reducing the risk of introducing vulnerabilities through custom redirection logic.

## Requirements

* [WordPress Environment]
* [Basic WordPress Knowledge]

## Support

* Email: barmosseri@gmail.com
* Linkedin: https://www.linkedin.com/in/barmosseri

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
