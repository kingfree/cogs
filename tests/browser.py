"""Optional Chromium regression. Set COGS_TEST_BROWSER to the Chromium executable."""
import os
from playwright.sync_api import sync_playwright

with sync_playwright() as playwright:
    browser = playwright.chromium.launch(executable_path=os.environ['COGS_TEST_BROWSER'], args=['--no-sandbox'])
    page = browser.new_page()
    base = os.environ['COGS_TEST_WEB_URL']
    # This installation normally serves editor/Bootstrap bundles from the parent vhost.
    page.goto(base+'/user/login.php', wait_until='domcontentloaded')
    login = page.locator('form#denglu')
    login.locator('[name="username"]').fill('student')
    login.locator('[name="password"]').fill(os.environ['COGS_TEST_LOGIN_PASSWORD'])
    with page.expect_response(lambda response: response.url.endswith('/user/dologin.php')) as submitted:
        login.get_by_role('button', name='登录').click()
    assert submitted.value.ok and '登录成功'.encode() in submitted.value.body()
    page.wait_for_url('**/user/panel.php')
    assert page.locator('[name="opwd"]').count() > 0
    page.goto(base+'/problem/index.php', wait_until='domcontentloaded')
    page.get_by_role('link', name="求和 O'Reilly", exact=True).first.click()
    page.get_by_text('计算和', exact=True).first.wait_for()
    # The browser must see one text-only link and one complete tooltip, including both kinds of quotes.
    for container in ('#fenlei', '#catebar'):
        label = page.locator(container + ' a[href$="index.php?caid=1"]')
        assert label.text_content() == '<b>算术</b> & 集合'
        assert label.locator('*').count() == 0
        assert label.get_attribute('title') == """a < b & "x" = 'y'"""
    browser.close()
print('Chromium 登录、题库链接和题面通过')
