/*
 * MIT Licence
 * Copyright (c) 2023 Simon Frankenberger
 *
 * Please see LICENCE.md for complete licence text.
 */
package eu.fraho.blm2.st;

import org.junit.jupiter.api.Assertions;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

import java.util.concurrent.TimeUnit;

public class GroupTests extends AbstractTest {
    private short counter = (short) (System.currentTimeMillis() % Short.MAX_VALUE);

    @Test
    void testCreateGroup() {
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();
        counter++;

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_223"));
    }

    @Test
    void testGroupChangeDescription() {
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();
        counter++;

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_223"));

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
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();
        counter++;

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_223"));
        Assertions.assertTrue(driver.findElement(By.id("group_image")).getDomAttribute("src").endsWith("&ts=0"));

        driver.findElement(By.id("gruppe_einstellungen")).click();
        setValue(By.id("new_pw1"), "foobar");
        setValue(By.id("new_pw2"), "foobar");
        driver.findElement(By.id("save_password")).submit();
        assertElementPresent(By.id("meldung_219"));

        resetPlayer(15);
        login("test5");

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
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();
        counter++;

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_223"));

        resetPlayer(15);
        login("test5");

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "Tx" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_141"));
    }

    @Test
    void testCreateGroupDuplicateTag() {
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();
        counter++;

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_223"));

        resetPlayer(15);
        login("test5");

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TGX" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_141"));
    }

    @Test
    void testJoinGroup() {
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();
        counter++;

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_223"));

        resetPlayer(15);
        login("test5");

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "TG" + counter);
        setValue(By.id("join_pwd"), "changeit");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_224"));
    }

    @Test
    void testJoinGroupWrongPassword() {
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();
        counter++;

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_223"));

        resetPlayer(15);
        login("test5");

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "TG" + counter);
        setValue(By.id("join_pwd"), "foobar");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_127"));
    }

    @Test
    void testJoinGroupNotFound() {
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("join_name"), "NonExistant");
        setValue(By.id("join_pwd"), "foobar");
        driver.findElement(By.id("join_group")).submit();
        assertElementPresent(By.id("meldung_127"));
    }

    @Test
    void testGroupCashDepositWithdraw() throws InterruptedException {
        resetPlayer(14);
        login("test4");
        WebDriver driver = getDriver();
        counter++;

        driver.findElement(By.id("link_gruppe")).click();
        setValue(By.id("create_name"), "TG" + counter);
        setValue(By.id("create_tag"), "T" + counter);
        setValue(By.id("create_pwd"), "changeit");
        driver.findElement(By.id("create_group")).submit();
        assertElementPresent(By.id("meldung_223"));

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
        assertText(By.id("gk_m_14"), "10,23 €");
        select(By.id("receiver"), "test4");
        setValue(By.id("amount"), "4,23");
        driver.findElement(By.id("gk_transfer")).submit();
        assertElementPresent(By.id("meldung_236"));

        assertText(By.id("gk_amount"), "In der Kasse befinden sich: 6,00 €");
        assertText(By.id("stat_money"), "4.994,00 €");

        driver.findElement(By.id("link_buero")).click();
        assertText(By.id("b_s_8"), "6,00 €");
    }
}
