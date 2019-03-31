<?php 
class NhlStats_Admin extends NhlStats_API
{
	use middleware;

	public static function init()
	{
		add_action('admin_menu', array('NhlStats_Admin', 'adminPages') );

		add_action('admin_enqueue_scripts', array('NhlStats_Admin', 'loadAdminScripts') );

		add_action('wp_ajax_by_player', array('NhlStats_Admin', 'searchByPlayer') );
		add_action('wp_ajax_player_stats', array('NhlStats_Admin', 'playerStats') );
	}

	public static function adminPages()
	{
		add_menu_page(
			'Hockey Stats',
			'Hockey Stats',
			'manage_options', 
			'hockey-stats', array('NhlStats_admin', 'loadViewHome'),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSIzMnB4IiBoZWlnaHQ9IjM2cHgiIHZpZXdCb3g9IjAgMCAzMiAzNiIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4gICAgICAgIDx0aXRsZT5Db21iaW5lZCBTaGFwZTwvdGl0bGU+ICAgIDxkZXNjPkNyZWF0ZWQgd2l0aCBTa2V0Y2guPC9kZXNjPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxwYXRoIGQ9Ik0zMC41MDM3NjI0LDE3LjI1ODYyMDEgTDMwLjU3Mzc4MjIsMTcuNDMxMzg4NiBDMzEuMDc0ODUxNSwxOC42NzY0NTQxIDMxLjQ4OTU4NDIsMTkuODYzMzUzNyAzMS40ODk1ODQyLDIxLjc4Mjk4NjkgQzMxLjQ4OTU4NDIsMjQuMzY5Nzk5MSAzMC41NzUzNjYzLDI2Ljc3NTY2ODEgMjguODQ2MDk5LDI4Ljc0MDQxOTIgQzI3LjI4LDMwLjUyMDYxMTQgMjUuMTIzODAyLDMxLjgzMTIzMTQgMjIuNzc1Mjg3MSwzMi40MzExMjY2IEMyMS42NzYxOTgsMzIuNzA2MjM1OCAyMC44NTM4NjE0LDMyLjg2MTg2OSAyMC4xMjgzMTY4LDMyLjk5OTI2NjQgQzE5LjM2NjY1MzUsMzMuMTQzNDIzNiAxOC43MDY2OTMxLDMzLjI2ODQwMTcgMTcuODg0ODMxNywzMy40OTQ3NzczIEMxNy41ODc2NDM2LDMzLjgwNzQ1ODUgMTYuMTc3MTA4OSwzNS4zMjAwODczIDE2LjE2MjM3NjIsMzUuMzM2MTIyMyBMMTYuMDQ2MDk5LDM1LjQ2MDk0MzIgTDE1LjkyOTk4MDIsMzUuMzM2MTIyMyBDMTUuOTE1MDg5MSwzNS4zMjAwODczIDE0LjUwNDIzNzYsMzMuODA2ODI5NyAxNC4yMDcyMDc5LDMzLjQ5NDc3NzMgQzEzLjM4NTM0NjUsMzMuMjY4NDAxNyAxMi43MjU4NjE0LDMzLjE0MzQyMzYgMTEuOTY0MTk4LDMyLjk5OTI2NjQgQzExLjIzODY1MzUsMzIuODYxODY5IDEwLjQxNjMxNjgsMzIuNzA2NTUwMiA5LjMxNjc1MjQ4LDMyLjQzMDgxMjIgQzUuNTM5MTY4MzIsMzEuNDY2MzU4MSAyLjQzMDczMjY3LDI4LjczMzUwMjIgMS4yMDQyNzcyMywyNS4yOTc5Mzg5IEMwLjgwNDU5NDA1OSwyNC4xNzk1ODA4IDAuNjAxOTgwMTk4LDIyLjk5NzA4MyAwLjYwMTk4MDE5OCwyMS43ODMzMDEzIEMwLjYwMTk4MDE5OCwxOS42OTgxMzEgMS4xMDI1NzQyNiwxOC40NTkzNTM3IDEuNTg3MDA5OSwxNy4yNjA4MjEgQzIuMDYzODQxNTgsMTYuMDgwNjgxMiAyLjUxNDIxNzgyLDE0Ljk2NjA5NjEgMi41MTQyMTc4MiwxMy4wODYyMzU4IEMyLjUxNDIxNzgyLDExLjY5OTM3MTIgMi4yMTkwODkxMSwxMC4zMzE1Mjg0IDEuNjM3MjI3NzIsOS4wMjA3NTEwOSBMMC42MzI1NTQ0NTUsNi43NTU0MjM1OCBMMC41Niw2LjU5MTkzMDEzIEwwLjczMjY3MzI2Nyw2LjU0MTMxMDA0IEwzLjA5NzAyOTcsNS44NDg4MjA5NiBDNC4wNzYwMzk2LDUuNTYyNTUwMjIgNC43OTA2NTM0Nyw0LjgyMzUyODM4IDUuMjIxODYxMzksMy42NTIzNDkzNCBMNi4yNjQyMzc2MiwwLjgxOTM1MzcxMiBMNi4zNDEyMjc3MiwwLjYxMDExMzUzNyBMNi41MTQwNTk0MSwwLjc1MjA2OTg2OSBMOC44Mzg0OTUwNSwyLjY2MTc5OTEzIEM5LjY0ODE1ODQyLDMuMzI3MjQ4OTEgMTAuNDc3NzgyMiwzLjY2NDYxMTM1IDExLjMwNDU1NDUsMy42NjQ2MTEzNSBDMTIuNjE5NDA1OSwzLjY2NDYxMTM1IDEzLjc4NTE4ODEsMi45Nzk2NjgxMiAxNC40Mzc4NjE0LDIuMzM4NTg1MTUgTDE1LjkzNDQxNTgsMC44NjkzNDQ5NzggTDE2LjA0NTc4MjIsMC43NTk5MzAxMzEgTDE2LjE1NzE0ODUsMC44NjkzNDQ5NzggTDE3LjY1NDAxOTgsMi4zMzg1ODUxNSBDMTguMzA2ODUxNSwyLjk3OTY2ODEyIDE5LjQ3MjYzMzcsMy42NjQ2MTEzNSAyMC43ODY4NTE1LDMuNjY0NjExMzUgQzIxLjYxMzQ2NTMsMy42NjQ2MTEzNSAyMi40NDMwODkxLDMuMzI3MDkxNyAyMy4yNTI1OTQxLDIuNjYxNjQxOTIgTDI1LjU3Njg3MTMsMC43NTE5MTI2NjQgTDI1Ljc0OTcwMywwLjYwOTk1NjMzMiBMMjUuODI2NjkzMSwwLjgxOTE5NjUwNyBMMjYuODY4OTEwOSwzLjY1MjAzNDkzIEMyNy4zMDA1OTQxLDQuODIzNTI4MzggMjguMDE1NTI0OCw1LjU2MjU1MDIyIDI4Ljk5Mzc0MjYsNS44NDg2NjM3NiBMMzEuMzU4MjU3NCw2LjU0MTE1Mjg0IEwzMS41MzA5MzA3LDYuNTkxNzcyOTMgTDMxLjQ1ODM3NjIsNi43NTUyNjYzOCBMMzAuNDUzNTQ0Niw5LjAyMDU5Mzg5IEMyOS44NzE2ODMyLDEwLjMzMTUyODQgMjkuNTc2NzEyOSwxMS42OTkyMTQgMjkuNTc2NzEyOSwxMy4wODYwNzg2IEMyOS41NzcxODgxLDE0Ljk2NTMxIDMwLjAyNzA4OTEsMTYuMDc5MjY2NCAzMC41MDM3NjI0LDE3LjI1ODYyMDEgWiBNMjkuODI3OTYwNCw3LjM4MzE0NDEgQzI5LjgyNzk2MDQsNy4zODMxNDQxIDI2LjE3ODY5MzEsNy4wNjc0NzU5OCAyNS4xODk4NjE0LDIuNjY0Nzg2MDMgQzI1LjE4OTg2MTQsMi42NjQ3ODYwMyAyMy4wMDE1MDUsNC45MDE1MDIxOCAyMC43ODMyMDc5LDQuOTAxNTAyMTggQzE3LjkyMzgwMiw0LjkwMTUwMjE4IDE2LjA0MTgyMTgsMi40ODkxODc3NyAxNi4wNDE4MjE4LDIuNDg5MTg3NzcgQzE2LjA0MTgyMTgsMi40ODkxODc3NyAxNC4xNTk4NDE2LDQuOTAxNTAyMTggMTEuMzAwMjc3Miw0LjkwMTUwMjE4IEM5LjA4MTgyMTc4LDQuOTAxNTAyMTggNi44OTM2MjM3NiwyLjY2NDc4NjAzIDYuODkzNjIzNzYsMi42NjQ3ODYwMyBDNS45MDQ2MzM2Niw3LjA2Nzk0NzYgMi4yNTQ3MzI2Nyw3LjM4MzE0NDEgMi4yNTQ3MzI2Nyw3LjM4MzE0NDEgQzIuMjU0NzMyNjcsNy4zODMxNDQxIDMuNzM5NTY0MzYsOS45OTAwNzg2IDMuNzM5NTY0MzYsMTMuMDc2OTYwNyBDMy43Mzk1NjQzNiwxMy40ODIzOTMgMy43MTg5NzAzLDEzLjg1NjIyNzEgMy42ODMxNjgzMiwxNC4yMDc4OTUyIEMzLjM2ODcxMjg3LDE3LjI5MTQ3NiAxLjgzMTEyODcxLDE4LjUyODM2NjggMS44MzExMjg3MSwyMS43ODI4Mjk3IEMxLjgzMTEyODcxLDI1Ljc3NTM3MTIgNC41ODcyNDc1MiwyOS45NjY5MzQ1IDkuNjE5MTY4MzIsMzEuMjMwMjM1OCBDMTIuMDEwMTM4NiwzMS44Mjk2NTk0IDEzLjY2MDM1NjQsMzEuOTgyNDYyOSAxNC44NzU0MDU5LDMyLjQxNzc2NDIgQzE1LjAzMDgxMTksMzIuNTYyMDc4NiAxNi4wNDE5ODAyLDMzLjY0OTYyNDUgMTYuMDQxOTgwMiwzMy42NDk2MjQ1IEMxNi4wNDE5ODAyLDMzLjY0OTYyNDUgMTcuMDUyODMxNywzMi41NjIwNzg2IDE3LjIwODA3OTIsMzIuNDE3NzY0MiBDMTguNDIzMTI4NywzMS45ODI0NjI5IDIwLjA3MzY2MzQsMzEuODI5ODE2NiAyMi40NjM4NDE2LDMxLjIzMDIzNTggQzI3LjQ5NjIzNzYsMjkuOTY2OTM0NSAzMC4yNTIwMzk2LDI1Ljc3NTg0MjggMzAuMjUyMDM5NiwyMS43ODI4Mjk3IEMzMC4yNTIwMzk2LDE4LjE1Nzk5MTMgMjguMzQzOTIwOCwxNy4wMzcxMTc5IDI4LjM0MzkyMDgsMTMuMDc3MTE3OSBDMjguMzQzNDQ1NSw5Ljk5MDA3ODYgMjkuODI3OTYwNCw3LjM4MzE0NDEgMjkuODI3OTYwNCw3LjM4MzE0NDEgWiBNMjkuMTc5NTY0NCwyMS43ODI5ODY5IEMyOS4xNzk1NjQ0LDI2LjM5ODA2MTEgMjUuNTU1ODAyLDI5LjMzNjA2OTkgMjIuMTk1OTYwNCwzMC4xODI2MjAxIEMxOS41MzUyMDc5LDMwLjg1MjQ3MTYgMTcuNzk1OTYwNCwzMC45MjkzNDUgMTYuNjI3NjQzNiwzMS40ODgzNjY4IEMxNi42Mjc2NDM2LDMxLjQ4ODM2NjggMTYuMjAyMjk3LDMxLjg3Mzk5MTMgMTYuMDQxODIxOCwzMi4wNTE0NzYgQzE1Ljg4MDU1NDUsMzEuODczOTkxMyAxNS40NTYsMzEuNDg4MzY2OCAxNS40NTYsMzEuNDg4MzY2OCBDMTQuMjg3NTI0OCwzMC45MjkzNDUgMTIuNTQ4NDM1NiwzMC44NTI0NzE2IDkuODg3NTI0NzUsMzAuMTgyNjIwMSBDNi41Mjc4NDE1OCwyOS4zMzU1OTgzIDIuOTAzNjAzOTYsMjYuMzk4MjE4MyAyLjkwMzYwMzk2LDIxLjc4Mjk4NjkgQzIuOTAzNjAzOTYsMTguNDgyMzA1NyA0LjgxMjk5MDEsMTcuMjcwMjUzMyA0LjgxMjk5MDEsMTMuMDc3Mjc1MSBDNC44MTI5OTAxLDEwLjI1MTk4MjUgMy43MzY3MTI4Nyw4LjA3NDg0NzE2IDMuNzM2NzEyODcsOC4wNzQ4NDcxNiBDMy43MzY3MTI4Nyw4LjA3NDg0NzE2IDYuMjIwNTE0ODUsNy41Nzc2MDY5OSA3LjM3NzU4NDE2LDQuNDU3NTU0NTkgQzcuMzc3NTg0MTYsNC40NTc1NTQ1OSA5LjAyNTU4NDE2LDUuOTg0MDE3NDcgMTEuMzAwNTk0MSw1Ljk4NDAxNzQ3IEMxNC4xNDk4NjE0LDUuOTg0MDE3NDcgMTYuMDQyMTM4NiwzLjk5ODY3MjQ5IDE2LjA0MjEzODYsMy45OTg2NzI0OSBDMTYuMDQyMTM4NiwzLjk5ODY3MjQ5IDE3LjkzNDA5OSw1Ljk4NDAxNzQ3IDIwLjc4MzUyNDgsNS45ODQwMTc0NyBDMjMuMDU4MjE3OCw1Ljk4NDAxNzQ3IDI0LjcwNjA1OTQsNC40NTc1NTQ1OSAyNC43MDYwNTk0LDQuNDU3NTU0NTkgQzI1Ljg2MzQ0NTUsNy41Nzc2MDY5OSAyOC4zNDc0MDU5LDguMDc0ODQ3MTYgMjguMzQ3NDA1OSw4LjA3NDg0NzE2IEMyOC4zNDc0MDU5LDguMDc0ODQ3MTYgMjcuMjcwNjUzNSwxMC4yNTIxMzk3IDI3LjI3MDY1MzUsMTMuMDc3Mjc1MSBDMjcuMjcwMDE5OCwxNy4yNzAyNTMzIDI5LjE3OTU2NDQsMTguNDgyMzA1NyAyOS4xNzk1NjQ0LDIxLjc4Mjk4NjkgWiBNMjAuNzgyODkxMSw3LjE5MzU1NDU5IEMxOC42NTUwNDk1LDcuMTkzNTU0NTkgMTYuOTkxODQxNiw2LjI3MjQ4OTA4IDE2LjA0MTM0NjUsNS41NzkwNTY3NyBDMTUuMDkwMjE3OCw2LjI3MjQ4OTA4IDEzLjQyNzQ4NTEsNy4xOTM1NTQ1OSAxMS4yOTk5NjA0LDcuMTkzNTU0NTkgQzkuODk1Mjg3MTMsNy4xOTM1NTQ1OSA4LjY5OTU2NDM2LDYuNzIxOTM4ODYgNy44NTY3OTIwOCw2LjI1MzYyNDQ1IEM3LjA3MjMxNjgzLDcuNTUzMjQwMTcgNi4wODgwNzkyMSw4LjMwODYxMTM1IDUuMzAzNzYyMzgsOC43MzU0MjM1OCBDNS42NDA4NzEyOSw5LjczNjk3ODE3IDYuMDMyMTU4NDIsMTEuMjg0NjYzOCA2LjAzMjE1ODQyLDEzLjA3Njk2MDcgQzYuMDMyMTU4NDIsMTYuNTI4NzE2MiA0Ljg0NDM1NjQ0LDE4LjE0NTQxNDggNC4zNDIzMzY2MywyMC4wOTkzMTg4IEwyMS43NjI4NTE1LDcuMTE3MzEwMDQgQzIxLjQ1MDI5Nyw3LjE2NTg4NjQ2IDIxLjEyMjY5MzEsNy4xOTM1NTQ1OSAyMC43ODI4OTExLDcuMTkzNTU0NTkgWiBNOS45NDg1MTQ4NSwyOC45NDU0MTQ4IEMxMC4wMjgwMzk2LDI4Ljk2NzczOCAxMC4xMDc4ODEyLDI4Ljk4OTc0NjcgMTAuMTg3MjQ3NSwyOS4wMDg5MjU4IEMxMi4yOTQ5NzAzLDI5LjU0MDc1MTEgMTQuODcyODcxMywyOS44MTQ2MDI2IDE1LjY1MTE2ODMsMzAuMjU2MTkyMSBDMTUuODI2ODUxNSwzMC4zODM2ODU2IDE2LjA0MTgyMTgsMzAuNjA5OTAzOSAxNi4wNDE4MjE4LDMwLjYwOTkwMzkgQzE2LjA0MTgyMTgsMzAuNjA5OTAzOSAxNi4yNTYzMTY4LDMwLjM4MzY4NTYgMTYuNDMxODQxNiwzMC4yNTYxOTIxIEMxNy4yMTA3NzIzLDI5LjgxNDYwMjYgMTkuNzg4NTE0OSwyOS41NDA3NTExIDIxLjg5NjM5NiwyOS4wMDg5MjU4IEMyNC44MTYzMTY4LDI4LjI3MTc5MDQgMjcuOTYwNzEyOSwyNS42OTgwMjYyIDI3Ljk2MDcxMjksMjEuNzgyNjcyNSBDMjcuOTYwNzEyOSwxOS44MzExMjY2IDI3LjA5ODEzODYsMTguNTQ2NjAyNiAyNi41MzIxMTg4LDE2LjU4NjA5NjEgTDkuOTQ4NTE0ODUsMjguOTQ1NDE0OCBaIE0yNi4wNTAzNzYyLDEzLjA3NzQzMjMgQzI2LjA1MDM3NjIsMTEuMjg1MTM1NCAyNi40NDIxMzg2LDkuNzM3MjkyNTggMjYuNzc4OTMwNyw4LjczNTg5NTIgQzI2LjEwNzg4MTIsOC4zNzAwNzg2IDI1LjI4ODM5Niw3Ljc2MzQyMzU4IDI0LjU3NTUyNDgsNi43ODA4OTA4MyBMNC4xMjY4OTEwOSwyMi4wMTk3MzggQzQuMjI0NjMzNjYsMjUuMDE2NTQxNSA2LjE3NjQ3NTI1LDI3LjE5ODU1MDIgOC40MDU1NDQ1NSwyOC4zMzU2MTU3IEwyNi4xOTc3MDMsMTUuMDc2NDU0MSBDMjYuMTA1OTgwMiwxNC40ODA4MDM1IDI2LjA1MDM3NjIsMTMuODIyMTEzNSAyNi4wNTAzNzYyLDEzLjA3NzQzMjMgWiBNMTMuMjE4NjkzMSwyMy40MDI5ODY5IEwxMS42MzY0MzU2LDI0LjU4MTU1NDYgTDkuOTI5OTgwMiwyMS45ODg2MTE0IEw5LjkyOTk4MDIsMjUuODU0MTMxIEw4LjQzMzEwODkxLDI2Ljk2OTM0NSBMOC40MzMxMDg5MSwyMS41MDI1MzI4IEM4LjQzMzEwODkxLDIwLjczMDE4MzQgNy42OTAxMzg2MSwyMC43MDk1ODk1IDcuNjkwMTM4NjEsMjAuNzA5NTg5NSBMOS44MTE4MDE5OCwxOS4xMjg0MTkyIEwxMS43MjIxMzg2LDIyLjAzMTY4NTYgTDExLjcyMjEzODYsMTcuNzA0NzY4NiBMMTMuMjE5MDA5OSwxNi41ODkyNDAyIEwxMy4yMTkwMDk5LDIzLjQwMjk4NjkgTDEzLjIxODY5MzEsMjMuNDAyOTg2OSBaIE0xOS4yNTU0NDU1LDE4LjkwNDU1OSBMMTcuNzkyLDE5Ljk5NTQwNjEgTDE3Ljc5MiwxNy41MDQwMTc1IEwxNi4xMDI5NzAzLDE4Ljc2MTk3MzggTDE2LjEwMjk3MDMsMjEuMjU0MTQ4NSBMMTQuNTY1MDY5MywyMi40MDAwMTc1IEwxNC41NjUwNjkzLDE2LjkzMjczMzYgQzE0LjU2NTA2OTMsMTYuMTYwMjI3MSAxMy44MjIwOTksMTYuMTM5NjMzMiAxMy44MjIwOTksMTYuMTM5NjMzMiBMMTYuMTAyOTcwMywxNC40NDAwODczIEwxNi4xMDI5NzAzLDE2Ljk1ODIwMDkgTDE3Ljc5MiwxNS43MDAwODczIEwxNy43OTIsMTMuMTgyNDQ1NCBMMTkuMjU1NDQ1NSwxMi4wOTAzNDA2IEwxOS4yNTU0NDU1LDE4LjkwNDU1OSBaIE0yNC43Nzg2MTM5LDE0Ljc4ODQ1NDEgTDIwLjUzMjkxMDksMTcuOTUyNTI0IEwyMC41MzI5MTA5LDEyLjQ4NTcxMTggQzIwLjUzMjkxMDksMTEuNzEyNzMzNiAxOS43ODk5NDA2LDExLjY5MjI5NjkgMTkuNzg5OTQwNiwxMS42OTIyOTY5IEwyMi4wNzA0OTUsOS45OTI3NTEwOSBMMjIuMDcwNDk1LDE1LjA2NzMzNjIgTDI0Ljc3ODc3MjMsMTMuMDQ5NzY0MiBDMjQuNzc4NjEzOSwxMy4wNDk3NjQyIDI0LjY4NjU3NDMsMTMuNjk4MDc4NiAyNC43Nzg2MTM5LDE0Ljc4ODQ1NDEgWiIgaWQ9IkNvbWJpbmVkLVNoYXBlIiBmaWxsPSIjRTRFNUU2Ij48L3BhdGg+ICAgIDwvZz48L3N2Zz4=',
			20
		);

		add_submenu_page(
			'hockey-stats',
			'Settings',
			'Settings',
			'manage_options', 
			'hockey-stats-settings',
			function()
			{
				if ($_POST['metricSystem']) {
					update_option('nhlstats_metricsystem', $_POST['metricSystem']);
				}
				require(AMB1_PLUGIN_PATH . 'views/settings.php');
			}
		);
	}	

	public static function loadAdminScripts()
	{
		wp_register_script( 'nhlstats_functions.js', plugin_dir_url( __FILE__ ) . '../static/js/functions.js', array('jquery'), PLUGIN_VERSION, true);
		wp_register_script( 'ss_ajax.js', plugin_dir_url( __FILE__ ) . '../static/js/ajax.js', array('jquery'), PLUGIN_VERSION, true);
		wp_enqueue_script( 'nhlstats_functions.js');
		
		wp_enqueue_script( 'ss_ajax.js');
		wp_enqueue_style( 'nhlstats-css', plugin_dir_url( __FILE__ ) . '../static/css/main.css', null, PLUGIN_VERSION);
	}

	public static function loadViewHome()
	{
		self::getPlayers();
		require(AMB1_PLUGIN_PATH . 'views/index.php');
	}
}