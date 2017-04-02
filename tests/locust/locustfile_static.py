import random

from locust import HttpLocust, TaskSet, task
from pyquery import PyQuery


class WalkPages(TaskSet):
    def __init__(self, parent):
        super(WalkPages, self).__init__(parent)
        self.urls_on_current_page = []
        self.toc_urls = []
        self.base_url = '/stage/en'

    def on_start(self):
        login_page = self.client.get(self.base_url + "/user/sign-in")

        pq = PyQuery(login_page.content)
        form_build_ids = pq('#user-login-form input[type="hidden"]:nth-child(3)')
        form_build_id_value = ''
        for form_build_id in form_build_ids:
            form_build_id_value = form_build_id.attrib["value"]
        result = self.client.post(self.base_url + "/user/sign-in", {
            "name": "stark@test.com",
            "pass": "1",
            "form_id": "user_login_form",
            'form_build_id': form_build_id_value,
            "op": "Submit"
        })

    @task(1)
    def load_group(self):
        r = self.client.get('/stage/en/country/finland')

    @task(1)
    def load_node(self):
        r = self.client.get('/stage/fr/news/news')

    @task(1)
    def load_group_document(self):
        r = self.client.get('/stage/en/group/1/documents')

class WebsiteUser(HttpLocust):
    task_set = WalkPages
    min_wait = 5000
    max_wait = 15000
