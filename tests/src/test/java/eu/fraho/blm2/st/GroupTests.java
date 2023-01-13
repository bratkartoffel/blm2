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

import java.util.concurrent.ThreadLocalRandom;

public class GroupTests extends AbstractTest {
    private static final int USER_ID = ThreadLocalRandom.current().nextInt(1_000_000);
    private short counter = (short) (System.currentTimeMillis() % Short.MAX_VALUE);

    @BeforeEach
    void beforeEach() {
        resetPlayer(USER_ID, getClass().getSimpleName());
        resetPlayer(USER_ID + 1, getClass().getSimpleName());
        login("test" + USER_ID);
    }

    @Test
    void testCreateGroup() {
        counter++;
        createGroup("TG" + counter, "T" + counter, "meldung_223");
    }

    @Test
    void testGroupChangeDescription() {
        counter++;
        WebDriver driver = getDriver();

        createGroup("TG" + counter, "T" + counter, "meldung_223");

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
        counter++;
        WebDriver driver = getDriver();

        createGroup("TG" + counter, "T" + counter, "meldung_223");
        Assertions.assertTrue(driver.findElement(By.id("group_image")).getDomAttribute("src").endsWith("&ts=0"));

        driver.findElement(By.id("gruppe_einstellungen")).click();
        setValue(By.id("new_pw1"), "foobar");
        setValue(By.id("new_pw2"), "foobar");
        driver.findElement(By.id("save_password")).submit();
        assertElementPresent(By.id("meldung_219"));

        login("test" + (USER_ID + 1));

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "TG" + counter);
        setValue(By.id("join_pwd"), "changeit");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_127"));

        setValue(By.id("join_pwd"), "foobar");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_224"));
    }

    @Test
    void testCreateGroupDuplicateName() {
        counter++;
        createGroup("TG" + counter, "T" + counter, "meldung_223");

        login("test" + (USER_ID + 1));
        createGroup("TG" + counter, "Tx" + counter, "meldung_141");
    }

    @Test
    void testCreateGroupDuplicateTag() {
        counter++;
        createGroup("TG" + counter, "T" + counter, "meldung_223");

        login("test" + (USER_ID + 1));
        createGroup("TGx" + counter, "T" + counter, "meldung_141");
    }

    @Test
    void testJoinGroup() {
        counter++;
        WebDriver driver = getDriver();
        createGroup("TG" + counter, "T" + counter, "meldung_223");

        login("test" + (USER_ID + 1));
        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "TG" + counter);
        setValue(By.id("join_pwd"), "changeit");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_224"));
    }

    @Test
    void testJoinGroupWrongPassword() {
        counter++;
        WebDriver driver = getDriver();
        createGroup("TG" + counter, "T" + counter, "meldung_223");

        login("test" + (USER_ID + 1));
        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "TG" + counter);
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
        counter++;
        WebDriver driver = getDriver();
        createGroup("TG" + counter, "T" + counter, "meldung_223");

        // deposit
        driver.findElement(By.id("link_bank")).click();
        driver.findElement(By.id("gruppen_kasse")).click();
        setValue(By.id("betrag"), "10,23");
        driver.findElement(By.id("do_transaction")).click();
        assertElementPresent(By.id("meldung_235"));

        // withdraw
        driver.findElement(By.id("link_gruppe")).click();
        driver.findElement(By.id("gruppe_kasse")).click();
        assertText(By.id("gk_amount"), "In der Kasse befinden sich: 10,23 €");
        assertText(By.id("gk_m_" + USER_ID), "10,23 €");
        select(By.id("receiver"), "test" + USER_ID);
        setValue(By.id("amount"), "4,23");
        driver.findElement(By.id("gk_transfer")).submit();
        assertElementPresent(By.id("meldung_236"));

        assertText(By.id("gk_amount"), "In der Kasse befinden sich: 6,00 €");
        assertText(By.id("stat_money"), "4.994,00 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_s_8"), "6,00 €");
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
