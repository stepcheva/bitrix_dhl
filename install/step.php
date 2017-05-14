<?php

if(!check_bitrix_sessid()) return;

echo CAdminMessage::ShowNote(GetMessage('DHL_INSTALL_SUCCESS'));
