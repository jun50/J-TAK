import requests
import re
import datetime
import time
from settings import *

session = requests.Session()

ltparmaid = {
    "action": "query",
    "meta": "tokens",
    "type": "login",
    "format": "json"
}

lt = session.get(BASE_NEKO, params={"action": "query", "meta": "tokens", "type": "login", "format": "json"}).json()['query']['tokens']['logintoken']
print(session.post(BASE_NEKO, data={"action": "login","lgname": USER, "lgpassword": PASSWORD, "lgtoken": lt, "format": "json"}).json())

nekonekopattern = re.compile(r'{{m\|(\S+?)}}')
nyassage = r"""{{利用者:jun50/メンション通知
    |ページ = %s
    |editer = %s
    |time = %s
}} ~~~~"""

def get_nyannyan():
    return session.get(BASE_NEKO, params={"action": "query", "list": "recentchanges", "format": "json", "rcprop": "user|ids|title|timestamp"}).json()["query"]["recentchanges"]

def get_nyattings(user):
    nyasult = session.get(BASE_NEKO, params={"action": "query", "prop": "revisions", "titles": f"利用者:{user}/J-TAK", "rvslots": "*", "rvprop": "content", "format": "json"}).json()
    return "-1" not in nyasult["query"]["pages"] and "true" in (list(nyasult["query"]["pages"].values())[0]["revisions"][0]["slots"]["main"]["*"]).lower()

meow = nyan = get_nyannyan()[0]["rcid"]

while True:
    for i in get_nyannyan():
        if i["rcid"] <= nyan:
            break
        if meow < i["rcid"]:
            meow = i["rcid"]
        if i["ns"] % 2 != 1 and i["ns"] != 4 and not i["title"].startswith("議論の場/"):
            continue
        print(i)

        if i["old_revid"] == 0:
            meoldpost = ""
        else:
            meoldpost = session.get(BASE_NEKO, params={"action": "query", "prop": "revisions", "rvprop": "content", "revids": i["old_revid"], "format": "json"}).json()["query"]["pages"][str(i["pageid"])]["revisions"][0]["*"]
        meowpost = session.get(BASE_NEKO, params={"action": "query", "prop": "revisions", "rvprop": "content", "revids": i["revid"], "format": "json"}).json()["query"]["pages"][str(i["pageid"])]["revisions"][0]["*"]

        meold_mention = nekonekopattern.findall(meoldpost)
        print(meold_mention)
        meow_mention = nekonekopattern.findall(meowpost)
        print(meow_mention)

        for nyantion in meold_mention:
            if nyantion in meow_mention:
                meow_mention.remove(nyantion)
        print(meow_mention)

        nyau = (datetime.datetime.fromisoformat(i["timestamp"][:-1]) + datetime.timedelta(hours=9)).strftime(r"%Y/%m/%d %H:%M")
        nyand_nyassage = nyassage % (i["title"], i["user"], nyau)

        for nyantion in meow_mention:
            if i["user"] in NYADMINS or get_nyattings(nyantion):
                print(nyantion, nyand_nyassage)
                nyasrf_token = session.get(url=BASE_NEKO, params={"action": "query", "meta": "tokens", "format": "json"}).json()["query"]["tokens"]["csrftoken"]
                session.post(BASE_NEKO, data={"action": "edit", "title": f"利用者・トーク:{nyantion}", "section": "new", "sectiontitle": "メンション通知", "text": nyand_nyassage, "token": nyasrf_token, "format": "json"})

    nyan = meow
    time.sleep(60)
