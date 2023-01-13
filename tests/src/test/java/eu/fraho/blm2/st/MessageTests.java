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

import java.util.concurrent.ThreadLocalRandom;

public class MessageTests extends AbstractTest {
    private static final int USER_ID = ThreadLocalRandom.current().nextInt(1_000_000);

    @BeforeEach
    void beforeEach() {
        resetPlayer(USER_ID, getClass().getSimpleName());
        resetPlayer(USER_ID + 1, getClass().getSimpleName());
        login("test" + USER_ID);
    }

    @Test
    void testSendAndReceiverReadMessage() {
        WebDriver driver = getDriver();
        long random = testSendMessageFromTest1ToTest2();

        Assertions.assertEquals("1", driver.findElement(By.id("MessagesOut")).getAttribute("data-count"));
        WebElement messageRow = driver.findElement(By.id("MessagesOut")).findElements(By.tagName("tr")).get(1);
        String messageId = messageRow.getAttribute("data-id");
        Assertions.assertEquals("Testmessage " + random, messageRow.findElements(By.tagName("td")).get(3).getText());

        login("test" + (USER_ID + 1));
        driver.findElement(By.id("link_nachrichten_liste")).click();
        driver.findElement(By.id("read_" + messageId)).click();
        assertText(By.id("sender"), "test" + USER_ID);
        assertText(By.id("receiver"), "test" + (USER_ID + 1));
        assertText(By.id("subject"), "Testmessage " + random);
        assertText(By.id("message"), "This is a testmessage from the systemtests\nWith linebreaks!\nand bbcode");
    }

    @Test
    void testDeleteUnreadMessage() {
        WebDriver driver = getDriver();
        testSendMessageFromTest1ToTest2();

        String messageId = driver.findElement(By.id("MessagesOut")).findElements(By.tagName("tr")).get(1).getAttribute("data-id");
        driver.findElement(By.id("delete_" + messageId)).click();
        assertElementPresent(By.id("meldung_211"));
    }

    @Test
    void testDeleteReadMessageAsSenderNotAllowed() {
        WebDriver driver = getDriver();
        testSendMessageFromTest1ToTest2();
        String messageId = driver.findElement(By.id("MessagesOut")).findElements(By.tagName("tr")).get(1).getAttribute("data-id");
        assertText(By.id("action_" + messageId), "Löschen");

        login("test" + (USER_ID + 1));
        driver.findElement(By.id("link_nachrichten_liste")).click();
        driver.findElement(By.id("read_" + messageId)).click();

        login("test" + USER_ID);
        driver.findElement(By.id("link_nachrichten_liste")).click();
        assertText(By.id("action_" + messageId), "");

        // try to delete by direct api call
        driver.get("http://localhost/actions/nachrichten.php?a=2&id=" + messageId + "&token=" + RANDOM_TOKEN);
        assertElementPresent(By.id("meldung_112"));
    }

    @Test
    void testDeleteMessageReceiver() {
        WebDriver driver = getDriver();
        testSendMessageFromTest1ToTest2();

        login("test" + (USER_ID + 1));
        driver.findElement(By.id("link_nachrichten_liste")).click();
        String messageId = driver.findElement(By.id("MessagesIn")).findElements(By.tagName("tr")).get(1).getAttribute("data-id");
        driver.findElement(By.id("delete_" + messageId)).click();
        assertElementPresent(By.id("meldung_211"));
    }

    @Test
    void testSendMessageSubjectTooShort() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_nachrichten_liste")).click();
        driver.findElement(By.id("new_message")).click();
        setValue(By.id("receiver"), "test" + (USER_ID + 1));
        setValue(By.id("subject"), "T");
        setValue(By.id("message"), "This is a testmessage from the systemtests\nWith linebreaks!\n[b]and bbcode[/b]");
        driver.findElement(By.id("send_message")).submit();
        assertElementPresent(By.id("meldung_128"));
    }

    @Test
    void testSendMessageBodyTooShort() {
        WebDriver driver = getDriver();
        driver.findElement(By.id("link_nachrichten_liste")).click();
        driver.findElement(By.id("new_message")).click();
        setValue(By.id("receiver"), "test" + (USER_ID + 1));
        setValue(By.id("subject"), "Testmessage");
        setValue(By.id("message"), "T");
        driver.findElement(By.id("send_message")).submit();
        assertElementPresent(By.id("meldung_128"));
    }

    private long testSendMessageFromTest1ToTest2() {
        WebDriver driver = getDriver();
        long random = System.currentTimeMillis();
        driver.findElement(By.id("link_nachrichten_liste")).click();
        driver.findElement(By.id("new_message")).click();
        setValue(By.id("receiver"), "test" + (USER_ID + 1));
        setValue(By.id("subject"), "Testmessage " + random);
        setValue(By.id("message"), "This is a testmessage from the systemtests\nWith linebreaks!\n[b]and bbcode[/b]");
        driver.findElement(By.id("send_message")).submit();
        assertElementPresent(By.id("meldung_204"));
        return random;
    }
}
