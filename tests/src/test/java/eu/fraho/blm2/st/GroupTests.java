/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

import java.util.List;

public class GroupTests extends AbstractTest {
    private final int userId1 = getNextUserId();
    private final int userId2 = getNextUserId();

    @BeforeEach
    void beforeEach() {
        resetPlayer(userId1, getClass().getSimpleName());
        resetPlayer(userId2, getClass().getSimpleName());
        login("test" + userId1);
    }

    @Test
    void testCreateGroup() {
        int groupId = getNextUserId();
        createGroup("TG" + groupId, String.valueOf(groupId), "meldung_223");
    }

    @Test
    void testGroupChangeDescription() {
        int groupId = getNextUserId();
        WebDriver driver = getDriver();

        createGroup("TG" + groupId, String.valueOf(groupId), "meldung_223");

        driver.findElement(By.id("gruppe_einstellungen")).click();
        setValue(By.id("beschreibung"), "Example description\n[b]with bbcode[/b]");
        driver.findElement(By.id("save_beschreibung")).submit();
        assertElementPresent(By.id("meldung_206"));

        driver.findElement(By.id("gruppe_board")).click();
        Assertions.assertEquals(driver.findElement(By.id("gruppe_beschreibung")).getDomProperty("innerHTML"),
                "Example description<br>\n<b>with bbcode</b>");
    }

    @Test
    void testGroupChangePassword() {
        int groupId = getNextUserId();
        WebDriver driver = getDriver();

        createGroup("TG" + groupId, String.valueOf(groupId), "meldung_223");
        Assertions.assertTrue(driver.findElement(By.id("group_image")).getDomAttribute("src").endsWith("&ts=0"));

        driver.findElement(By.id("gruppe_einstellungen")).click();
        setValue(By.id("new_pw1"), "foobar");
        setValue(By.id("new_pw2"), "foobar");
        driver.findElement(By.id("save_password")).submit();
        assertElementPresent(By.id("meldung_219"));

        login("test" + userId2);

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "TG" + groupId);
        setValue(By.id("join_pwd"), "changeit");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_127"));

        setValue(By.id("join_pwd"), "foobar");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_224"));
    }

    @Test
    void testCreateGroupDuplicateName() {
        int groupId1 = getNextUserId();
        int groupId2 = getNextUserId();
        createGroup("TG" + groupId1, String.valueOf(groupId1), "meldung_223");

        login("test" + userId2);
        createGroup("TG" + groupId1, String.valueOf(groupId2), "meldung_141");
    }

    @Test
    void testCreateGroupDuplicateTag() {
        int groupId1 = getNextUserId();
        int groupId2 = getNextUserId();
        createGroup("TG" + groupId1, String.valueOf(groupId1), "meldung_223");

        login("test" + userId2);
        createGroup("TGx" + groupId2, String.valueOf(groupId1), "meldung_141");
    }

    @Test
    void testJoinGroup() {
        int groupId = getNextUserId();
        WebDriver driver = getDriver();
        createGroup("TG" + groupId, String.valueOf(groupId), "meldung_223");

        login("test" + userId2);
        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "TG" + groupId);
        setValue(By.id("join_pwd"), "changeit");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_224"));
    }

    @Test
    void testJoinGroupWrongPassword() {
        int groupId = getNextUserId();
        WebDriver driver = getDriver();
        createGroup("TG" + groupId, String.valueOf(groupId), "meldung_223");

        login("test" + userId2);
        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "TG" + groupId);
        setValue(By.id("join_pwd"), "foobar");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_127"));
    }

    @Test
    void testJoinGroupNotFound() {
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "NonExistant");
        setValue(By.id("join_pwd"), "foobar");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_127"));
    }

    @Test
    void testGroupCashDepositWithdraw() {
        int groupId = getNextUserId();
        WebDriver driver = getDriver();
        createGroup("TG" + groupId, String.valueOf(groupId), "meldung_223");

        // deposit
        driver.findElement(By.id("link_bank")).click();
        driver.findElement(By.id("gruppen_kasse")).click();
        setValue(By.id("betrag"), "10.23");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_235"));

        // withdraw
        driver.findElement(By.id("link_gruppe")).click();
        driver.findElement(By.id("gruppe_kasse")).click();
        assertText(By.id("gk_amount"), "In der Kasse befinden sich: 10.23 €");
        assertText(By.id("gk_m_" + userId1), "10.23 €");
        selectByText(By.id("receiver"), "test" + userId1);
        setValue(By.id("amount"), "4.23");
        driver.findElement(By.id("gk_transfer")).submit();
        assertElementPresent(By.id("meldung_236"));

        assertText(By.id("gk_amount"), "In der Kasse befinden sich: 6.00 €");
        assertText(By.id("stat_money"), "4,994.00 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_s_8"), "6.00 €");
    }

    @Test
    void testGroupNapAccept() {
        int groupId1 = getNextUserId();
        int groupId2 = getNextUserId();
        WebDriver driver = getDriver();
        // as user1
        {
            createGroup("TG" + groupId1, String.valueOf(groupId1), "meldung_223");
        }

        // as user2
        {
            login("test" + userId2);
            createGroup("TG" + groupId2, String.valueOf(groupId2), "meldung_223");
            driver.findElement(By.id("gruppe_diplomatie")).click();
            selectByValue(By.id("relation_typ"), "1");
            setValue(By.id("group"), "TG" + groupId1);
            driver.findElement(By.id("send_diplomacy_offer")).submit();
            assertElementPresent(By.id("meldung_229"));
        }

        // as user1
        {
            login("test" + userId1);
            assertText(By.id("link_gruppe"), "Gruppe (0 / 1)");
            driver.findElement(By.id("link_gruppe")).click();
            assertText(By.id("gruppe_diplomatie"), "Diplomatie (1)");
            driver.findElement(By.id("gruppe_diplomatie")).click();

            Assertions.assertEquals("1", driver.findElement(By.id("IncomingRequests")).getAttribute("data-count"));
            List<WebElement> links = driver.findElement(By.id("IncomingRequests")).findElements(By.tagName("a"));
            Assertions.assertEquals(3, links.size());
            Assertions.assertEquals("TG" + groupId2, links.get(0).getText());

            // accept
            links.get(1).click();
            assertElementPresent(By.id("meldung_231"));
            Assertions.assertEquals("1", driver.findElement(By.id("diplomacy_NAP")).getAttribute("data-count"));
        }

        // as user2
        {
            login("test" + userId2);
            driver.findElement(By.id("link_gruppe")).click();
            driver.findElement(By.id("gruppe_diplomatie")).click();
            Assertions.assertEquals("1", driver.findElement(By.id("diplomacy_NAP")).getAttribute("data-count"));
        }
    }

    @Test
    void testGroupBndRefuse() {
        int groupId1 = getNextUserId();
        int groupId2 = getNextUserId();
        WebDriver driver = getDriver();
        // as user1
        {
            createGroup("TG" + groupId1, String.valueOf(groupId1), "meldung_223");
        }

        // as user2
        {
            login("test" + userId2);
            createGroup("TG" + groupId2, String.valueOf(groupId2), "meldung_223");
            driver.findElement(By.id("gruppe_diplomatie")).click();
            selectByValue(By.id("relation_typ"), "2");
            setValue(By.id("group"), "TG" + groupId1);
            driver.findElement(By.id("send_diplomacy_offer")).submit();
            assertElementPresent(By.id("meldung_229"));
        }

        // as user1
        {
            login("test" + userId1);
            assertText(By.id("link_gruppe"), "Gruppe (0 / 1)");
            driver.findElement(By.id("link_gruppe")).click();
            assertText(By.id("gruppe_diplomatie"), "Diplomatie (1)");
            driver.findElement(By.id("gruppe_diplomatie")).click();

            Assertions.assertEquals("1", driver.findElement(By.id("IncomingRequests")).getAttribute("data-count"));
            List<WebElement> links = driver.findElement(By.id("IncomingRequests")).findElements(By.tagName("a"));
            Assertions.assertEquals(3, links.size());
            Assertions.assertEquals("TG" + groupId2, links.get(0).getText());

            // refuse
            links.get(2).click();
            assertElementPresent(By.id("meldung_216"));
            Assertions.assertEquals("0", driver.findElement(By.id("diplomacy_BND")).getAttribute("data-count"));
        }

        // as user2
        {
            login("test" + userId2);
            driver.findElement(By.id("link_gruppe")).click();
            driver.findElement(By.id("gruppe_diplomatie")).click();
            Assertions.assertEquals("0", driver.findElement(By.id("diplomacy_BND")).getAttribute("data-count"));
        }
    }

    private void createGroup(String name, String tag, String expectedMessage) {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), name);
        setValue(By.id("create_tag"), tag);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id(expectedMessage));
    }
}
